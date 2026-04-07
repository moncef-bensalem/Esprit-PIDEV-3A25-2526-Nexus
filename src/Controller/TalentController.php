<?php

namespace App\Controller;

use App\Entity\Talent;
use App\Entity\Competence;
use App\Form\TalentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/talent')]
final class TalentController extends AbstractController
{
    #[Route(name: 'app_talent_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $allTalents = $entityManager->getRepository(Talent::class)->findAll();

        // Récupérer les paramètres de recherche
        $search       = trim($request->query->get('q', ''));
        $filterDept   = $request->query->get('departement', '');
        $filterPoste  = $request->query->get('poste', '');
        $filterExp    = $request->query->get('experience', '');

        // Filtre dynamique
        $talent = array_filter($allTalents, function (Talent $t) use ($search, $filterDept, $filterPoste, $filterExp) {
            if ($search !== '') {
                $haystack = strtolower($t->getNom().' '.$t->getPrenom().' '.$t->getPoste().' '.$t->getDepartement());
                if (strpos($haystack, strtolower($search)) === false) return false;
            }
            if ($filterDept  !== '' && strtolower($t->getDepartement()) !== strtolower($filterDept))  return false;
            if ($filterPoste !== '' && strtolower($t->getPoste())       !== strtolower($filterPoste)) return false;
            if ($filterExp   !== '') {
                $exp = $t->getAnneesExperience() ?? 0;
                if ($filterExp === '0-2'  && !($exp >= 0  && $exp <= 2))  return false;
                if ($filterExp === '3-5'  && !($exp >= 3  && $exp <= 5))  return false;
                if ($filterExp === '6-10' && !($exp >= 6  && $exp <= 10)) return false;
                if ($filterExp === '10+'  && !($exp > 10))                return false;
            }
            return true;
        });
        $talent = array_values($talent);

        // Données pour les listes de filtres
        $departements = array_unique(array_map(fn(Talent $t) => $t->getDepartement(), $allTalents));
        sort($departements);
        $postes = array_unique(array_map(fn(Talent $t) => $t->getPoste(), $allTalents));
        sort($postes);

        $competencesCount = $entityManager->getRepository(Competence::class)->count([]);

        return $this->render('talent/index.html.twig', [
            'talent'            => $talent,
            'total_talents'     => count($allTalents),
            'talents_affiches'  => count($talent),
            'departements_count'=> count($departements),
            'competences_count' => $competencesCount,
            'departements'      => $departements,
            'postes'            => $postes,
            'search'            => $search,
            'filter_dept'       => $filterDept,
            'filter_poste'      => $filterPoste,
            'filter_exp'        => $filterExp,
        ]);
    }

    #[Route('/classements', name: 'app_talent_classements', methods: ['GET'])]
    public function classements(EntityManagerInterface $entityManager): Response
    {
        $allTalents = $entityManager->getRepository(Talent::class)->findAll();

        // Classement par expérience
        $byExp = $allTalents;
        usort($byExp, fn(Talent $a, Talent $b) => ($b->getAnneesExperience() ?? 0) <=> ($a->getAnneesExperience() ?? 0));

        // Stats par département
        $byDept = [];
        foreach ($allTalents as $t) {
            $d = $t->getDepartement();
            if (!isset($byDept[$d])) $byDept[$d] = 0;
            $byDept[$d]++;
        }
        arsort($byDept);

        // Stats par poste
        $byPoste = [];
        foreach ($allTalents as $t) {
            $p = $t->getPoste();
            if (!isset($byPoste[$p])) $byPoste[$p] = 0;
            $byPoste[$p]++;
        }
        arsort($byPoste);

        return $this->render('talent/classements.html.twig', [
            'talents_by_exp'  => array_slice($byExp, 0, 10),
            'by_departement'  => $byDept,
            'by_poste'        => $byPoste,
            'total'           => count($allTalents),
        ]);
    }

    #[Route('/statistiques', name: 'app_talent_statistiques', methods: ['GET'])]
    public function statistiques(EntityManagerInterface $entityManager): Response
    {
        $allTalents = $entityManager->getRepository(Talent::class)->findAll();

        $byDept = [];
        $expBuckets = ['0-2 ans' => 0, '3-5 ans' => 0, '6-10 ans' => 0, '10+ ans' => 0];
        $byNiveau = [];

        foreach ($allTalents as $t) {
            // Département
            $d = $t->getDepartement();
            if (!isset($byDept[$d])) $byDept[$d] = 0;
            $byDept[$d]++;

            // Expérience
            $exp = $t->getAnneesExperience() ?? 0;
            if ($exp <= 2)       $expBuckets['0-2 ans']++;
            elseif ($exp <= 5)   $expBuckets['3-5 ans']++;
            elseif ($exp <= 10)  $expBuckets['6-10 ans']++;
            else                 $expBuckets['10+ ans']++;

            // Niveau d'études
            $n = $t->getNiveauEtudes() ?? 'Non précisé';
            if (!isset($byNiveau[$n])) $byNiveau[$n] = 0;
            $byNiveau[$n]++;
        }

        $avgExp = count($allTalents) > 0
            ? round(array_sum(array_map(fn($t) => $t->getAnneesExperience() ?? 0, $allTalents)) / count($allTalents), 1)
            : 0;

        return $this->render('talent/statistiques.html.twig', [
            'total'       => count($allTalents),
            'by_dept'     => $byDept,
            'exp_buckets' => $expBuckets,
            'by_niveau'   => $byNiveau,
            'avg_exp'     => $avgExp,
            'nb_depts'    => count($byDept),
        ]);
    }

    #[Route('/new', name: 'app_talent_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $talent = new Talent();
        $form = $this->createForm(TalentType::class, $talent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($talent);
            $entityManager->flush();

            return $this->redirectToRoute('app_talent_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('talent/new.html.twig', [
            'talent' => $talent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_talent_show', methods: ['GET'])]
    public function show(Talent $talent): Response
    {
        return $this->render('talent/show.html.twig', [
            'talent' => $talent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_talent_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Talent $talent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TalentType::class, $talent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_talent_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('talent/edit.html.twig', [
            'talent' => $talent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_talent_delete', methods: ['POST'])]
    public function delete(Request $request, Talent $talent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$talent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($talent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_talent_index', [], Response::HTTP_SEE_OTHER);
    }
}
