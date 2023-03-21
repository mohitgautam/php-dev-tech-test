<?php

namespace App\Controller;

use App\Service\CompanyMatcher;
use App\Service\CompanyCreditsDeductor;
use App\Service\CompanyCreditsManager;


class FormController extends Controller
{
    public function index()
    {
        $this->render('form.twig');
    }

    public function submit(array $request)
    {
        @[
            "postcode" => $postcode,
            "bedrooms" => $bedrooms,
            "type" => $type
        ] = $request;

        $matcher = new CompanyMatcher($this->db());
        $matchedCompanies = $matcher->match($postcode, $bedrooms, $type)
            ->pick($_ENV['MAX_MATCHED_COMPANIES'])
            ->results();

        $credsManager = new CompanyCreditsManager($this->db());
        $credsManager->deduct(array_column($matchedCompanies, 'id'));

        $this->render('results.twig', [
            'matchedCompanies' => $matchedCompanies,
        ]);
    }
}