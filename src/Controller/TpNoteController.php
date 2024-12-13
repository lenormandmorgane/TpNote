<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class TpNoteController extends AbstractController
{
    #[Route('/user', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setPhoneNumber($data['phoneNumber']);

        $user->setRoles($data['roles']);

        $em->persist($user);
        $em->flush();

        return $this->json(
            ['message' => 'User créé avec succès'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/user', name: 'get_user', methods: ['GET'])]
    public function fetchUser(Request $request, EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phoneNumber' => $user->getPhoneNumber(),
                'roles' => $user->getRoles(),
                'reservation' => $user->getReservations()->map(fn(Reservation $reservation) => $reservation->getEventName())->getValues()
            ];
        }
        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    #[Route('/user/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(
                ['message' => 'User introuvable'],
                Response::HTTP_NOT_FOUND
            );
        }
        $data = json_decode($request->getContent(), true);
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPhoneNumber($data['phoneNumber']);


        $em->flush();
        return $this->json(
            ['message' => 'User modifier avec succès'],
            Response::HTTP_OK
        );
    }

    #[Route('/user/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(
                ['message' => 'User introuvable'],
                Response::HTTP_NOT_FOUND
            );
        }
        $em->remove($user);
        $em->flush();
        return $this->json(
            ['message' => 'User Supprimer'],
            Response::HTTP_OK
        );
    }

    #[Route('/add-reservation', name: 'add_reservation', methods: ['POST'])]
    public function addReservation(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $eventName = $data['eventName'];

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $reservation = $em->getRepository(Reservation::class)->findOneBy(['eventName' => $eventName]);

        if (!$user) {
            return $this->json(['error' => 'User introuvable'], 404);
        }

        if (!$reservation) {
            return $this->json(['error' => 'Reservation introuvable'], 404);
        }

        $now = new \DateTime();
        if ($reservation->getDate() < $now->modify('+24 hours')) {
            return $this->json(['error' => 'reservation doivent etre faites au moins 24 heures en avance '], Response::HTTP_BAD_REQUEST);
        }

        $reservation->setRelations($user);
        $em->persist($reservation);
        $em->flush();

        return $this->json(['success' => 'user bien ajouter a la reservation']);
    }

    #[Route('/get-reservations', name: 'get_reservations', methods: ['GET'])]
    public function getReservations(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data["email"];


        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['error' => 'User introuvable'], 404);
        }

        $reservations = $user->getReservations();
        $reservationData = [];
        foreach ($reservations as $reservation) {
            $reservationData[] = [
                'id' => $reservation->getId(),
                'eventName' => $reservation->getEventName(),
                'date' => $reservation->getDate()->format('Y-m-d H:i:s'),
                'timeSlot' => $reservation->getTimeSlot()->format('%h hours %i minutes'),
            ];
        }

        return $this->json(['reservations' => $reservationData]);
    }


}
