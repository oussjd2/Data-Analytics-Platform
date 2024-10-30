<?php

namespace App\DataFixtures;

use App\Entity\SuperUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperUserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $superUser = new SuperUser();
        $superUser->setEmail('superuser@example.com');
        $superUser->setRoles(['ROLE_SUPER_USER']);
        $superUser->setPassword($this->passwordHasher->hashPassword($superUser, 'superpassword'));

        $manager->persist($superUser);
        $manager->flush();
    }
}
