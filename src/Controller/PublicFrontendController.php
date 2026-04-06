<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Candidature;
use App\Entity\OffreEmploi;
use App\Form\PostulationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PublicFrontendController extends AbstractController
{
    #[Route('/offres', name: 'app_public_offres', methods: ['GET'])]
    public function offres(EntityManagerInterface $em): Response
    {
        $offres = $em->getRepository(OffreEmploi::class)->findBy(['statut_offre' => 'PUBLIEE'], ['date_creation' => 'DESC']);
        
        return $this->render('frontend/offres.html.twig', [
            'offres' => $offres
        ]);
    }

    #[Route('/postuler/{id_offre}', name: 'app_public_postuler', methods: ['GET', 'POST'])]
    public function postuler(string $id_offre, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $offre = $em->getRepository(OffreEmploi::class)->find($id_offre);
        if (!$offre || $offre->getStatut_offre() !== 'PUBLIEE') {
            throw $this->createNotFoundException('Cette offre n\'est plus disponible.');
        }

        $form = $this->createForm(PostulationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $nom = $data['nom_complet'];
            $email = $data['email'];

            /** @var UploadedFile $cvFile */
            $cvFile = $form->get('cv')->getData();
            $newFilename = null;

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$cvFile->guessExtension();

                try {
                    $cvFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/cvs',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                    return $this->redirectToRoute('app_public_postuler', ['id_offre' => $id_offre]);
                }
            }

            $candidat = $em->getRepository(Candidat::class)->findOneBy(['email_contact' => $email]);
            if (!$candidat) {
                $candidat = new Candidat();
                $candidat->setEmailContact($email);
                $candidat->setDateAjout(new \DateTime());
            }
            $candidat->setNomComplet($nom);
            if ($newFilename) {
                $candidat->setCheminCv($newFilename);
            }
            $em->persist($candidat);

            $existingCandidature = $em->getRepository(Candidature::class)->findOneBy([
                'candidat' => $candidat,
                'offre_emploi' => $offre
            ]);

            if ($existingCandidature) {
                $this->addFlash('error', 'Vous avez déjà postulé à cette offre.');
                return $this->redirectToRoute('app_public_offres');
            }

            $candidature = new Candidature();
            $candidature->setCandidat($candidat);
            $candidature->setOffreEmploi($offre);
            $candidature->setDatePostulation(new \DateTime());
            $candidature->setEtatAvancement('RECU');
            $candidature->setSourceCandidature('Portail Public');
            
            $em->persist($candidature);
            $em->flush();

            $this->addFlash('success', 'Votre candidature a été envoyée avec succès! Vous pouvez la suivre via notre portail.');
            return $this->redirectToRoute('app_public_suivi', ['email' => $email]);
        }

        return $this->render('frontend/postuler.html.twig', [
            'form' => $form,
            'offre' => $offre
        ]);
    }

    #[Route('/suivi', name: 'app_public_suivi', methods: ['GET', 'POST'])]
    public function suivi(Request $request, EntityManagerInterface $em): Response
    {
        $email = $request->query->get('email') ?? $request->request->get('email');
        $candidatures = [];
        $candidat = null;

        if ($email) {
            $candidat = $em->getRepository(Candidat::class)->findOneBy(['email_contact' => $email]);
            if ($candidat) {
                $candidatures = $em->getRepository(Candidature::class)->findBy(['candidat' => $candidat], ['date_postulation' => 'DESC']);
            } else if ($request->isMethod('POST')) {
                $this->addFlash('error', 'Aucun compte trouvé avec cet email.');
            }
        }

        return $this->render('frontend/suivi.html.twig', [
            'candidatures' => $candidatures,
            'candidat' => $candidat,
            'searchedEmail' => $email
        ]);
    }
}
