<?php
namespace App\Contracts\Repositories;

interface TicketRepository extends Repository
{
    public function getStats();
    public function getDailyTotalAmount($searchParams);
}
