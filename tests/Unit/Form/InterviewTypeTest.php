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
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

/**
 * @group unit
 */
class InterviewTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return [
            new PreloadedExtension([new InterviewType()], []),
            new ValidatorExtension($validator),
        ];
    }

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

        $interview = (new Interview())
            ->setGuid($guid)
            ->setFramework($framework)
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);
        $form = $this->factory->create(InterviewType::class, $interview);

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
        self::assertEquals($expected, $interview);
    }

    public function testFormView(): void
    {
        $interview = (new Interview())
            ->setGuid((string) Uuid::v4())
            ->setFramework((new Framework())
                ->addLevel((new Level())->setName('level_1'))
                ->addLevel((new Level())->setName('level_2'))
                ->addLevel((new Level())->setName('level_3'))
                ->addCategory((new Category())->setName('category_1')
                    ->addSkill((new Skill())->setGuid((string) Uuid::v4())->setName('skill_1'))))
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $view = $this->factory->create(InterviewType::class, $interview)->createView();

        $this->assertSame($interview, $view->vars['value']);
    }
}
