<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Season;
use App\Enum\FlashType;
use App\Helpers\Base64;
use App\Repository\CandidateRepository;
use App\Security\Voter\SeasonVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

#[AsController]
#[IsGranted('ROLE_USER')]
final class EliminationController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    #[Route('/elimination/{seasonCode}', name: 'app_elimination')]
    #[IsGranted(SeasonVoter::ELIMINATION, 'season')]
    public function index(#[MapEntity] Season $season): Response
    {
        return $this->render('elimination/index.html.twig', [
            'controller_name' => 'EliminationController',
        ]);
    }

    #[Route('/elimination/{seasonCode}/{candidateHash}', name: 'app_elimination_cadidate')]
    #[IsGranted(SeasonVoter::ELIMINATION, 'season')]
    public function candidateScreen(Season $season, string $candidateHash, CandidateRepository $candidateRepository): Response
    {
        $candidate = $candidateRepository->getCandidateByHash($season, $candidateHash);
        if (!$candidate instanceof Candidate) {
            $this->addFlash(FlashType::Warning,
                t('Cound not find candidate with name %name%', ['%name%' => Base64::base64UrlDecode($candidateHash)])->trans($this->translator)
            );
            throw new \InvalidArgumentException('Candidate not found');
        }

        return $this->render('elimination/candidate.html.twig', [
            'season' => $season,
            'candidate' => $candidate,
        ]);
    }
}
