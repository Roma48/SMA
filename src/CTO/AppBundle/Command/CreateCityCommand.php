<?php

namespace CTO\AppBundle\Command;

use CTO\AppBundle\Entity\City;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CreateCityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cto:create:city')
            ->setDescription('Creating city.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $cityName = $dialog->ask($output,
            'Enter city name [Черкаси]: ',
            'Черкаси'
        );

        $output->writeln("");
        $output->writeln("You entered:");
        $output->writeln("city name is: " . $cityName);
        $output->writeln("");

        /** @var QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion('it\'s ok? [y/N]: ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $city = new City();

        $city->setName(trim($cityName));

        $em->persist($city);
        $em->flush();

        $output->writeln("");
        $output->writeln(" Created successfully. ");
    }
}
