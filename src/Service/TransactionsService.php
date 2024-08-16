<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransactionsService {

  /**
   * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
   */
  private ParameterBagInterface $parameterBag;

  private array $transactions = [];

  private array $users = [];

  public array $currencyRates = [];

  public function __construct(ParameterBagInterface $parameterBag, private HttpClientInterface $client) {
    $this->parameterBag = $parameterBag;
  }

  /**
   * Method to load CSV file and return information about transactions.
   *
   * @return array
   */
  public function loadAllTransactions(): array {
    $this->currencyRates = $this->loadCurrencyRates();
    $transactionsFilePath = $this->parameterBag->get('kernel.project_dir') . '/files/transactions.csv';

    if (($handle = fopen($transactionsFilePath, "r")) !== FALSE) {
      while ((list($date, $id, $type, $operation, $amount, $currency) = fgetcsv($handle, 0, ",")) != FALSE) {
        if (!array_key_exists($id, $this->users)) {
          $this->users[$id] = new User($id);
        }

        $transaction = new Transaction($id, $date, $type, $operation, $amount, $currency);

        $this->users[$id]->addTransaction($transaction, $this->currencyRates);

        $transaction->setFee($this->getTransactionFee($transaction));

        $this->transactions[] = $transaction;
      }
      fclose($handle);
    }

    return $this->transactions;
  }

  /**
   * Method for get array with all transactions fee.
   *
   * @return array
   */
  public function getAllTransactionsFee(): array {
    $transactions = $this->loadAllTransactions();
    $result = [];

    foreach ($transactions as $transaction) {
      $result[] = $transaction->getFee();
    }

    return $result;
  }

  /**
   * Method to get fee of transaction.
   *
   * @param $transaction
   *
   * @return float
   */
  public function getTransactionFee($transaction): float {
    if ("deposit" === $transaction->getOperation()) {
      $fee = $this->getDepositFee($transaction);
    }
    else {
      $fee = "business" === $transaction->getType() ? $this->getBusinessWithdrawFee($transaction) : $this->getPrivateWithdrawFee($transaction, $this->users[$transaction->getId()]);
    }

    return $fee;
  }

  /**
   * Method to get fee for deposit.
   *
   * @param $transaction
   *
   * @return float
   */
  public function getDepositFee($transaction): float {
    $fee = number_format(($transaction->getAmount() / 100) * 0.03, 2, '.', '');
    return round($fee, 2, PHP_ROUND_HALF_UP);
  }

  /**
   * Method to get a fee for business client's withdrawal.
   *
   * @param $transaction
   *
   * @return float
   */
  private function getBusinessWithdrawFee($transaction): float {
    $fee = number_format(($transaction->getAmount() / 100) * 0.5, 2, '.', '');
    return round($fee, 2, PHP_ROUND_HALF_UP);
  }

  /**
   * Method to get a fee for private client's withdrawal.
   *
   * @param $transaction
   * @param $user
   *
   * @return float
   */
  private function getPrivateWithdrawFee($transaction, $user): float {
    $date = $transaction->getDate();
    $date = DateTime::createFromFormat("Y-m-d", $date);
    $week = $date->format("W o");

    $currency = $transaction->getCurrency();
    $amount = $transaction->getAmount();

    $freeCharge = $user->getFreeCharge();
    $history = $user->getWithdrawTransactionHistory($week);
    $transactionWeekLimit = $user->getWithdrawTransactionHistoryWeekLimit($week);
    $operations = count($history["amounts"]);
    $summa = 0;

    foreach ($history["amounts"] as $value) {
      $summa += $value;
    }

    if ($operations <= 3) {
      if (!$transactionWeekLimit) {
        if ($summa > $freeCharge) {
          $user->setWithdrawTransactionHistoryWeekLimit($week, TRUE);
          $fee = $summa - $freeCharge;
        }
        else {
          $fee = 0;
        }
      }
      else {
        $fee = $amount;
      }
    }
    else {
      $fee = $amount;
    }

    if ("EUR" != $currency) {
      $fee = $fee * $this->currencyRates[$currency];
    }

    $fee = number_format(($fee / 100) * 0.3, 2, '.', '');
    return round($fee, 2, PHP_ROUND_HALF_UP);
  }

  /**
   * Method for loading the currencies rates.
   *
   * @return array
   * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
   */
  public function loadCurrencyRates(): array {
    $response = $this->client->request(
      'GET',
      sprintf("https://api.exchangeratesapi.io/latest?access_key=%s", $_ENV['EXCHANGEAPI_FREE_KEY'] ?? "e804a038c557140fe691ca5f4ac0c4c3"));
    print_r($response->toArray(), 1);
    return $response->toArray()["rates"];
  }

}