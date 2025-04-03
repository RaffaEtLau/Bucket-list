<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Wish;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment')]
final class CommentController extends AbstractController
{

    #[Route('/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, WishRepository $wishRepository): Response
    {
        $wishId = $request->query->get('id');

        $wish = $wishRepository->find($wishId);

        if (!$wish) {
            throw $this->createNotFoundException('Le souhait demandé n\'existe pas');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $comment = new Comment();
        $comment->setWish($wish);
        $comment->setUser($this->getUser());
        $comment->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté');

            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'wish' => $wish,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if($this->getUser() !== $comment->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce commentaire.');
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été modifié !');

            return $this->redirectToRoute('wish_detail', ['id' => $comment->getWish()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if($this->getUser() !== $comment->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce commentaire.');
        }

        $wishId = $comment->getWish()->getId();

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé.');
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
