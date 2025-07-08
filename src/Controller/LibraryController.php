<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        /** @var Book[] $books */
        $books = $bookRepository->findAll();

        return $this->render('library/index.html.twig', ['books' => $books]);
    }

    #[Route('/library/new', name: 'library_new')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', 'The book has been created');
            return $this->redirectToRoute('library_one', ['id' => $book->getId()]);
        }

        return $this->render('library/create.html.twig', ['form' => $form]);
    }

    #[Route('/library/reset', name: 'library_reset', methods: ['GET'])]
    public function reset(EntityManagerInterface $em): RedirectResponse
    {
        $em->createQuery('DELETE FROM App\Entity\Book b')->execute();

        $books = [
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'publisher' => 'Penguin Essentials',
                'isbn' => '9780141036144',
                'year' => 2008,
                'image' => 'https://www.adlibris.com/images/9780141036144/1984.jpg'
            ],
            [
                'title' => 'Brave New World',
                'author' => 'Aldous Huxley',
                'publisher' => 'Vintage',
                'isbn' => '9780099477464',
                'year' => 2004,
                'image' => 'https://www.adlibris.com/images/9780099477464/brave-new-world.jpg'
            ],
            [
                'title' => 'Fahrenheit 451',
                'author' => 'Ray Bradbury',
                'publisher' => 'HarperCollins UK',
                'isbn' => '9780006546061',
                'year' => 1993,
                'image' => 'https://www.adlibris.com/images/9780006546061/fahrenheit-451.jpg'
            ]
        ];

        foreach ($books as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setAuthor($data['author']);
            $book->setPublisher($data['publisher']);
            $book->setIsbn($data['isbn']);
            $book->setYear($data['year']);
            $book->setImage($data['image']);
            $em->persist($book);
        }

        $em->flush();

        $this->addFlash('success', 'Demo library resetted');

        return $this->redirectToRoute('library_index');
    }

    #[Route('/library/{id}', name: 'library_one', methods: ['GET'])]
    public function one(BookRepository $bookRepository, int $id): Response
    {
        /** @var Book|null $book */
        $book = $bookRepository->find($id);

        if (!$book) {
            $this->addFlash('info', 'No such book');
            return $this->redirectToRoute('library_index');
        }

        return $this->render('library/details.html.twig', ['book' => $book]);
    }

    #[Route('/library/{id}/edit', name: 'library_edit')]
    public function edit(Request $request, Book $book, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'The book has been updated');
            return $this->redirectToRoute('library_one', ['id' => $book->getId()]);
        }

        return $this->render('library/edit.html.twig', [
                    'form' => $form->createView(),
                    'book' => $book,
                ]);
    }

    #[Route('/library/{id}/delete', name: 'library_delete', methods: ['POST'])]
    public function delete(
        Book $book,
        ManagerRegistry $doctrine,
    ): RedirectResponse {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($book);
        $entityManager->flush();
        $this->addFlash('success', 'The book has been deleted');

        return $this->redirectToRoute('library_index');
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_isbn', methods: ['GET'])]
    public function apiGetBookByISBN(
        string $isbn,
        BookRepository $bookRepository
    ): JsonResponse {

        /** @var Book|null $book */
        $book = $bookRepository->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], 404);
        }

        return new JsonResponse([
            'title'     => $book->getTitle(),
            'author'    => $book->getAuthor(),
            'publisher' => $book->getPublisher(),
            'isbn'      => $book->getIsbn(),
            'year'      => $book->getYear(),
            'image'     => $book->getImage(),
        ]);
    }

    #[Route('/api/library/books', name: 'api_library_index', methods: ['GET'])]
    public function apiGetAllBooks(BookRepository $bookRepository): JsonResponse
    {
        /** @var Book[] $books */
        $books = $bookRepository->findAll();

        $data = [];

        foreach ($books as $book) {
            $data[] = [
                'title'     => $book->getTitle(),
                'author'    => $book->getAuthor(),
                'publisher' => $book->getPublisher(),
                'isbn'      => $book->getIsbn(),
                'year'      => $book->getYear(),
                'image'     => $book->getImage(),
            ];
        }

        return new JsonResponse($data);
    }
}
