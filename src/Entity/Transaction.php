<?php

namespace App\Entity;

class Transaction {

  public float $fee;

  public function __construct(
    private int $id,
    private string $date,
    private string $type,
    private string $operation,
    private float $amount,
    private string $currency,
  ) {

  }

  /**
   * @return int
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * @param float $fee
   */
  public function setFee(float $fee): void {
    $this->fee = $fee;
  }

  /**
   * @return float
   */
  public function getFee(): float {
    return $this->fee;
  }

  /**
   * @return string
   */
  public function getDate(): string {
    return $this->date;
  }

  /**
   * @return string
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getOperation(): string {
    return $this->operation;
  }

  /**
   * @return string
   */
  public function getAmount(): string {
    return $this->amount;
  }

  /**
   * @return string
   */
  public function getCurrency(): string {
    return $this->currency;
  }
}