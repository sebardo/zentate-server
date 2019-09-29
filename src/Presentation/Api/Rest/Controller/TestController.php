<?php
namespace App\Presentation\Api\Rest\Controller;

use App\Domain\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class TestController
 * @package App\Presentation\Api\Rest\Controller
 */
final class TestController extends AbstractController//implements TokenAuthenticatedController
{
    /**
     * @Route("test", name="api_test", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getTest(Request $request): Response
    {
        $id = $request->get('oauth_user_id');
        return new JsonResponse($id, Response::HTTP_OK);
    }

    /**
     * @Route("user-create", name="api_user_create", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function createUser(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em): Response
    {

        $user = User::create('user@email.com', 'user');

        if($user instanceof User){
            //password
            $plainPassword = 'user';
            $encoded = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encoded);
            $user->setActive(true);
            $user->setRoles(array('ROLE_USER'));
            $em->persist($user);
            $em->flush();
        }
        die('User created');
    }
}