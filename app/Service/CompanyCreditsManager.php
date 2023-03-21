<?php

namespace App\Service;

class CompanyCreditsManager
{
    private \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function deduct(array $companyIds): void
    {
        $isClause = implode(',', array_fill(0, count($companyIds), '?'));

        $query = "UPDATE companies
                  SET credits = credits - 1 
                  WHERE companies.id IN ({$isClause})";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute($companyIds);
        }catch (\Exception $e){

        }
    }

}