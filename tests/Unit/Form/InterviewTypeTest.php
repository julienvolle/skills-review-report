<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Category;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\Level;
use App\Entity\Skill;
use App\Form\InterviewType;
use DateTime;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class InterviewTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $datetime = new DateTime();
        $formData = [
            'title'     => 'title',
            'firstname' => 'firstname',
            'lastname'  => 'lastname',
            'createdAt' => $datetime->format('Y-m-d H:i:s'),
            'result'    => [$guid => 0.0],
        ];

        $framework = (new Framework())
            ->addLevel((new Level())->setName('level_1'))
            ->addLevel((new Level())->setName('level_2'))
            ->addLevel((new Level())->setName('level_3'))
            ->addCategory((new Category())->setName('category_1')
                ->addSkill((new Skill())->setGuid($guid)->setName('skill_1')));

        $model = (new Interview())
            ->setGuid($guid)
            ->setFramework($framework)
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);
        $form = $this->factory->create(InterviewType::class, $model);

        $expected = (new Interview())
            ->setGuid($guid)
            ->setFramework($framework)
            ->setTitle($formData['title'])
            ->setFirstname($formData['firstname'])
            ->setLastname($formData['lastname'])
            ->setResult($formData['result'])
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }
}
