<?php

namespace App\Controller;

use App\Service\TransactionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommissionController extends AbstractController {

  #[Route('/', name: 'app_homepage')]
  public function calculation(TransactionsService $transactionsService): Response {
    $transactions = $transactionsService->loadAllTransactions();

    return $this->render('commission/calculation.html.twig', [
      'transactions' => $transactions,
    ]);
  }

}