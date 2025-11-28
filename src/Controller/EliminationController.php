<?php

declare(strict_types=1);

namespace Tvdt\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Elimination;
use Tvdt\Enum\FlashType;
use Tvdt\Form\EliminationEnterNameType;
use Tvdt\Helpers\Base64;
use Tvdt\Repository\CandidateRepository;
use Tvdt\Security\Voter\SeasonVoter;

use function Symfony\Component\Translation\t;

#[AsController]
#[IsGranted('ROLE_USER')]
final class EliminationController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly CandidateRepository $candidateRepository) {}

    #[IsGranted(SeasonVoter::ELIMINATION, 'elimination')]
    #[Route('/elimination/{elimination}', name: 'tvdt_elimination', requirements: ['elimination' => Requirement::UUID])]
    public function index(#[MapEntity] Elimination $elimination, Request $request): Response
    {
        $form = $this->createForm(EliminationEnterNameType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();

            return $this->redirectToRoute('tvdt_elimination_candidate', ['elimination' => $elimination->id, 'candidateHash' => Base64::base64UrlEncode($name)]);
        }

        return $this->render('quiz/elimination/index.html.twig', [
            'form' => $form,
            'controller_name' => 'EliminationController',
        ]);
    }

    #[IsGranted(SeasonVoter::ELIMINATION, 'elimination')]
    #[Route('/elimination/{elimination}/{candidateHash}', name: 'tvdt_elimination_candidate', requirements: ['elimination' => Requirement::UUID, 'candidateHash' => self::CANDIDATE_HASH_REGEX])]
    public function candidateScreen(Elimination $elimination, string $candidateHash): Response
    {
        $candidate = $this->candidateRepository->getCandidateByHash($elimination->quiz->season, $candidateHash);
        if (!$candidate instanceof Candidate) {
            $this->addFlash(FlashType::Warning,
                t('Cound not find candidate with name %name%', ['%name%' => Base64::base64UrlDecode($candidateHash)])->trans($this->translator),
            );

            return $this->redirectToRoute('tvdt_elimination', ['elimination' => $elimination->id]);
        }

        $screenColour = $elimination->getScreenColour($candidate->name);

        if (null === $screenColour) {
            $this->addFlash(FlashType::Warning, $this->translator->trans('Cound not find candidate with name %name% in elimination.', ['%name%' => $candidate->name]));

            return $this->redirectToRoute('tvdt_elimination', ['elimination' => $elimination->id]);
        }

        return $this->render('quiz/elimination/candidate.html.twig', [
            'candidate' => $candidate,
            'colour' => $screenColour,
        ]);
    }
}
