<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\CategoryRepository;
use App\Repository\WishRepository;
use App\Services\Upload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/wishes', name: 'wish_')]
class WishController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(WishRepository $wishRepository, Request $request, CategoryRepository $categoryRepository): Response
    {
        // Récupérer toutes les catégories pour le filtre
        $categories = $categoryRepository->findAll();

        // Récupérer la catégorie sélectionnée dans le filtre (s'il y en a une)
        $categoryId = $request->query->get('category');
        $selectedCategory = null;

        // Si une catégorie est sélectionnée, on filtre les souhaits
        if ($categoryId) {
            $selectedCategory = $categoryRepository->find($categoryId);
            if ($selectedCategory) {
                $wishes = $wishRepository->findBy(
                    ['isPublished' => true, 'category' => $selectedCategory],
                    ['dateCreated' => 'DESC']
                );
            } else {
                $wishes = $wishRepository->findBy(
                    ['isPublished' => true],
                    ['dateCreated' => 'DESC']
                );
            }
        } else {
            // Sinon on affiche tous les souhaits publiés
            $wishes = $wishRepository->findBy(
                ['isPublished' => true],
                ['dateCreated' => 'DESC']
            );
        }

        return $this->render('wish/list.html.twig', [
            'wishes' => $wishes,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'], requirements: ['id'=>'\d+'])]
    public function detail(Wish $wish): Response
    {
        return $this->render('wish/detail.html.twig', [
            'wish' => $wish,
            ]);
    }

    #[Route('/create', name: 'create', methods: ['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // notre entité vide
        $wish = new Wish();
        // notre formulaire, associée à l'entité vide
        $wishForm = $this->createForm(WishType::class, $wish);
        // récupère les données du form et les injecte dans notre $wish
        $wishForm->handleRequest($request);

        // si le formulaire est soumis et valide...
        if ($wishForm->isSubmitted() && $wishForm->isValid()){
            // Gérer l'upload d'image
            $imageFile = $wishForm->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try{
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/images/wish',
                        $newFilename
                    );

                    $wish->setImageName($newFilename);
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe mal pendant l'upload
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload de l\'image');
                }
            }

            // sauvegarde en bdd
            $em->persist($wish);
            $em->flush();

            // affiche un message sur la prochaine page
            $this->addFlash('success', 'Idea successfully added!');

            // redirige vers la page de détails de l'idée fraîchement créée
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }

        // affiche le formulaire
        return $this->render('wish/create.html.twig', [
            'wishForm' => $wishForm->createView()
        ]);
    }

    #[Route('/{id}/update', name: 'update', requirements: ['id'=>'\d+'],
        methods: ['GET','POST'])]
    public function update(Wish $wish, Request $request,
                           EntityManagerInterface $em, SluggerInterface $slugger, Upload $upload): Response
    {
        // notre formulaire, associée à l'entité vide
        $wishForm = $this->createForm(WishType::class, $wish);
        // récupère les données du form et les injecte dans notre $wish
        $wishForm->handleRequest($request);
        // si le formulaire est soumis et valide...
        if ($wishForm->isSubmitted() && $wishForm->isValid()){
            //Gérer l'upload d'image
            $imageFile = $wishForm->get('imageFile')->getData();
            //Si une nouvelle image est uploadée
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/images/wish',
                        $newFilename
                    );

                    $upload->removeImage($wish);

                    $wish->setImageName($newFilename);
                } catch (FileException $e) {
                    //Gérer l'exception
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload de l\'image');
                }
            }

            //Gérer la suppression d'image si la case est cachée
            if ($wishForm->has('deleteImage')&&$wishForm->get('deleteImage')->getData()) {
                if ($wish->getImageName()) {
                    $imagePath = $this->getParameter('kernel.project_dir').'/public/uploads/images/wish/'.$wish->getImageName();
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    $wish->setImageName(null);
                }
            }
            // sauvegarde en bdd
            $em->flush();
            // affiche un message sur la prochaine page
            $this->addFlash('success', 'Idea successfully updated!');
            // redirige vers la page de détail de l'idée fraîchement modifiée
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }
        // affiche le formulaire
        return $this->render('wish/create.html.twig', [
            'wishForm' => $wishForm
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id'=>'\d+'],
        methods: ['GET'])]
    public function delete(Wish $wish, Request $request,
                           EntityManagerInterface $em, Upload $upload): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wish->getId(), $request->query->get('token'),)) {
            try {
                if ($wish->getImageName()) {
                    $upload->removeImage($wish);
                }
                // Suppression de l'entité
                $em->remove($wish);
                $em->flush();
                $this->addFlash('success', 'This wish has been deleted');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur: ' . $e->getMessage());
            }
        }
        // on retourne à la page de la liste
        return $this->redirectToRoute('wish_list');
    }



}
