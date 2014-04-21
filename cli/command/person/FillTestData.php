<?php

namespace popcorn\cli\command\person;

use popcorn\model\persons\Person;
use popcorn\model\persons\PersonBuilder;
use popcorn\model\persons\PersonFactory;
use popcorn\model\voting\VotingFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

const randomEnglishNames = 'Jonh Aaron Ace Alan Alec Hector Loris Roger Robin Roberto Henri Lukas Ross Dan Mack Marc Homer Ian Harry Curt Dallas Irwin Jason Stan Stefan Dean';
const randomNames = 'Джон Аарон Эйс Алан Алек Гектор Лорис Роджер Робин Роберто Генри Лукас Роз Дэн Мак Марк Гомер Ян Гарри Курт Даллас Ирвин Джейсон Стэн Стефан';
const randomLastName = 'Смит Джонс Тейлор Уиллсон Робертс Робинсон Томпсон Грин Хол Вудс Моррис Джексон Вокер Эванс Дэвис Паркер Белл Митчелл Морган Вотсон Бейкер Хилл';

class FillTestData extends Command {

    protected function configure() {

        $this
        ->setName('person:fill:testData')
        ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL
            )
        ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE
            )
        ->setDescription('Заполнение БД случайными персонами');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $limit = (int)$input->getOption('limit') ? : 10;

        for($i = 0; $i <= $limit; $i++) {
            $names1 = explode(' ', randomNames);
            $names2 = explode(' ', randomLastName);

            $name = sprintf('%s %s', $names1[array_rand($names1)], $names2[array_rand($names2)]);

			$person = new Person();
			$person->setName($name);
			$person->setEnglishName('Jonh');
			$person->setBirthDate(new \DateTime(sprintf('%04u-%02u-%02u 00:00:00', rand(1940, 2005), rand(1, 12), rand(1, 28))));
			$person->setLook(VotingFactory::createTenVoting());
			$person->setStyle(VotingFactory::createTenVoting());
			$person->setTalent(VotingFactory::createTenVoting());

			PersonFactory::savePerson($person);



            if($input->getOption('debug')) {
                $output->writeln(sprintf("<info>Персона \"%s\" добавлена [%u/%u]</info>", $name, $i, $limit));
            }
        }

    }
}