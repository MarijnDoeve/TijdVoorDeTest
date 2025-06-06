<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Elimination;
use App\Enum\FlashType;
use App\Form\EliminationEnterNameType;
use App\Helpers\Base64;
use App\Repository\CandidateRepository;
use App\Security\Voter\SeasonVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/elimination/{elimination}', name: 'app_elimination')]
    #[IsGranted(SeasonVoter::ELIMINATION, 'elimination')]
    public function index(#[MapEntity] Elimination $elimination, Request $request): Response
    {
        $form = $this->createForm(EliminationEnterNameType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();

            return $this->redirectToRoute('app_elimination_candidate', ['elimination' => $elimination->getId(), 'candidateHash' => Base64::base64UrlEncode($name)]);
        }

        return $this->render('quiz/elimination/index.html.twig', [
            'form' => $form,
            'controller_name' => 'EliminationController',
        ]);
    }

    #[Route('/elimination/{elimination}/{candidateHash}', name: 'app_elimination_candidate')]
    #[IsGranted(SeasonVoter::ELIMINATION, 'elimination')]
    public function candidateScreen(Elimination $elimination, string $candidateHash, CandidateRepository $candidateRepository): Response
    {
        $candidate = $candidateRepository->getCandidateByHash($elimination->getQuiz()->getSeason(), $candidateHash);
        if (!$candidate instanceof Candidate) {
            $this->addFlash(FlashType::Warning,
                t('Cound not find candidate with name %name%', ['%name%' => Base64::base64UrlDecode($candidateHash)])->trans($this->translator),
            );

            return $this->redirectToRoute('app_elimination', ['elimination' => $elimination->getId()]);
        }

        $screenColour = $elimination->getScreenColour($candidate->getName());

        if (null === $screenColour) {
            $this->addFlash(FlashType::Warning, $this->translator->trans('Cound not find candidate with name %name% in elimination.', ['%name%' => $candidate->getName()]));

            return $this->redirectToRoute('app_elimination', ['elimination' => $elimination->getId()]);
        }

        return $this->render('quiz/elimination/candidate.html.twig', [
            'candidate' => $candidate,
            'colour' => $screenColour,
        ]);
    }
}
