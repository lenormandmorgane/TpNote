<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'create_reservation', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, ): Response
    {
        $data = json_decode($request->getContent(), true);

        $reservation = new Reservation();
        $date = new \DateTime($data['date']);
        $reservation->setDate($date);
        $reservation->setEventName($data['eventName']);

        $timeSlot = new \DateInterval($data['timeSlot']);
        $reservation->setTimeSlot($timeSlot);



        $em->persist($reservation);
        $em->flush();

        return $this->json(
            ['message' => 'reservation créé avec succès'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/reservation', name: 'get_reservation', methods: ['GET'])]
    public function fetchreservation(Request $request, EntityManagerInterface $em): Response
    {
        $reservations = $em->getRepository(Reservation::class)->findAll();
        $data = [];
        foreach ($reservations as $reservation) {
            $data[] = [
                'id' => $reservation->getId(),
                'eventName' => $reservation->getEventName(),
                'date' => $reservation->getDate(),
                'timeSlot' => $reservation->getTimeSlot(),
            ];
        }
        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }
}
