<?php

namespace App\Service;

class CompanyMatcher
{
    private \PDO $db;
    private string $postcodePref;
    private string $bedrooms;
    private string $type;
    private int $limit;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function match(string $pcode = null, string $bedrooms = null, string $type = null): CompanyMatcher
    {
        preg_match('/([A-Z]{1,2})\d+/', $pcode, $matches);
        $this->postcodePref = $matches[1] ?? null;
        $this->bedrooms = $bedrooms;
        $this->type = $type;
        return $this;
    }

    public function pick(int $limit): CompanyMatcher
    {
        $this->limit = $limit;
        return $this;
    }

    public function results(): array
    {
        $query = $this->buildQuery();
        $stmt = $this->db->prepare($query);

        if ($this->postcodePref) {
            $stmt->bindValue(':postcodePref', '%' . $this->postcodePref . '%');
        }

        if ($this->bedrooms) {
            $stmt->bindValue(':bedrooms', '%' . $this->bedrooms . '%');
        }

        if ($this->type) {
            $stmt->bindValue(':type', $this->type);
        }

        if ($this->limit) {
            $stmt->bindValue(':limit', $this->limit, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }


    private function buildQuery(): string
    {
        $query = 'SELECT * FROM `companies` as c
                  INNER JOIN `company_matching_settings` ON c.`id` = `company_matching_settings`.`company_id`
                  WHERE c.`credits` > 0';

        $query .= ($this->postcodePref) ? ' AND `postcodes` LIKE :postcodePref' : '';
        $query .= ($this->bedrooms) ? ' AND `bedrooms` LIKE :bedrooms' : '';
        $query .= ($this->type) ? ' AND `type` = :type' : '';
        $query .= ' ORDER BY RAND()';
        $query .= ($this->limit) ? ' LIMIT :limit' : '';
        return $query;
    }
}
