<?php

namespace App\Entity;

use DateTime;

class User {

  private array $transactions = [];

  private int $freeCharge = 1000;

  private array $withdrawTransactionHistory = [];

  public function __construct(private readonly int $id) {}

  /**
   * Method for adding new transactions to the User entity.
   *
   * @param $transaction
   * @param $rates
   *
   * @return void
   */
  public function addTransaction($transaction, $rates): void {
    $this->transactions[] = $transaction;

    if ("withdraw" == $transaction->getOperation()) {
      $this->updateWithdrawTransactionHistory($transaction, $rates);
    }
  }

  /**
   * Method for create\update history of withdraw transactions.
   *
   * @param $transaction
   * @param $rates
   *
   * @return void
   */
  private function updateWithdrawTransactionHistory($transaction, $rates): void {
    $date = $transaction->getDate();
    $date = DateTime::createFromFormat("Y-m-d", $date);
    $amount = $transaction->getAmount();

    if ("EUR" != $currency = $transaction->getCurrency()) {
      $amount = $amount / $rates[$currency];
    }
    $this->withdrawTransactionHistory[$date->format("W o")]["amounts"][] = $amount;
  }

  /**
   * Method for get history of withdraw transactions.
   *
   * @param $week
   *
   * @return array
   */
  public function getWithdrawTransactionHistory($week): array {
    return $this->withdrawTransactionHistory[$week];
  }

  /**
   * Method for getting a week limit from the history of withdrawal
   * transactions.
   *
   * @param $week
   *
   * @return bool|NULL
   */
  public function getWithdrawTransactionHistoryWeekLimit($week): null|bool {
    return $this->withdrawTransactionHistory[$week]["limit"] ?? NULL;
  }

  /**
   * Method for setting a week limit to the history of withdrawal transactions.
   *
   * @param $week
   * @param $limit
   *
   * @return void
   */
  public function setWithdrawTransactionHistoryWeekLimit($week, $limit): void {
    $this->withdrawTransactionHistory[$week]["limit"] = $limit;
  }

  /**
   * Get free charge variable.
   *
   * @return int
   */
  public function getFreeCharge(): int {
    return $this->freeCharge;
  }

  /**
   * Get the id of user.
   *
   * @return int
   */
  public function getId(): int {
    return $this->id;
  }

}