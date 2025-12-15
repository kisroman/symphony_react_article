<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /**
     * @param string $userApiKey
     */
    public function __construct(
        private readonly string $userApiKey,
    ) {
    }
    /**
     * @param Request $request
     * @param UserService $userService
     *
     * @return Response
     * @throws \Random\RandomException
     */
    #[Route('/user/register', name: 'user_register', methods: ['GET', 'POST'])]
    public function create(Request $request, UserService $userService): Response
    {
        //echo $this->userApiKey;

        $userData = new User();
        $form = $this->createForm(UserType::class, $userData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userService->createAndFlush(
                $userData->getUsername(),
                $userData->getFirstName(),
                $userData->getLastName(),
                UserService::ROLE_BLOGGER,
                $userData->getPassword()
            );

            return $this->redirectToRoute('user_register_congratulation', [
                'id' => $user->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please fix the validation errors.');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    #[Route('/user/register/congratulation/{id}', name: 'user_register_congratulation', requirements: ['id' => '\d+'],
        methods: ['GET'])]
    public function congratulation(int $id): Response
    {
        return $this->render('user/congratulation.html.twig', [
            'userId' => $id,
        ]);
    }
}
