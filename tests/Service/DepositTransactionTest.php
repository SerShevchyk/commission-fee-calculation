<?php

namespace App\Tests\Service;

use App\Entity\Transaction;
use App\Service\TransactionsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DepositTransactionTest extends TestCase {

  private TransactionsService $transactionsService;

  /**
   * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
   */
  protected function setUp(): void {
    $parameterBagMock = $this->createMock(ParameterBagInterface::class);
    $httpClientMock = $this->createMock(HttpClientInterface::class);

    $this->transactionsService = new TransactionsService($parameterBagMock, $httpClientMock);
  }

  public function testDepositTransactionFee(): void {

    $transaction = new Transaction(1, 2016 - 01 - 05, "private", "deposit", 200.00, "EUR");

    $fee = $this->transactionsService->getTransactionFee($transaction);

    $this->assertSame(0.06, $fee);
  }

}