<?php

namespace App\DataFixtures;

use App\Entity\Leads\Leads;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class LeadsFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = FakerFactory::create();

        for ($i = 0; $i < 50; $i++) {
            $lead = new Leads();
            $lead->setPorteur($faker->company)
                ->setFirstname($faker->firstName)
                ->setBirthday($faker->dateTimeBetween('-80 years', '-18 years'))
                ->setPhone($faker->phoneNumber)
                ->setEmail($faker->email)
                ->setQuestion($faker->text(100))  // Using text() instead of sentence()
                ->setTitle($faker->word)  // Using word() instead of title()
                ->setCountry($faker->country)
                ->setIp($faker->ipv4)
                ->setPartner($faker->company)
                ->setRaison($faker->word)
                ->setSource($faker->randomElement(['web', 'app', 'referral']))
                ->setCampaign($faker->word)
                ->setRtc($faker->randomElement(['yes', 'no']))
                ->setAudio($faker->randomElement(['enabled', 'disabled']))
                ->setSubid($faker->uuid)
                ->setForm($faker->randomElement(['A', 'B', 'C']))
                ->setCardlist($faker->word)  // Using word() instead of creditCardType()
                ->setC1($faker->word)
                ->setC2($faker->word)
                ->setC3($faker->word)
                ->setReponseAi($faker->text)  // Using text() instead of paragraph()
                ->setCreatedAt($faker->dateTimeThisYear)
                ->setExported($faker->boolean);

            $manager->persist($lead);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['leads'];
    }
}