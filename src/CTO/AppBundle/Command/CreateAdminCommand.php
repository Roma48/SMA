<?php

namespace CTO\AppBundle\Command;

use CTO\AppBundle\Entity\AdminUser;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CreateAdminCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cto:create:admin')
            ->setDescription('Creating admin user for CTO admin dashboard.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $email = $dialog->ask($output,
            'Enter e-mail [admin@local.io]: ',
            'admin@local.io'
        );
        $firstName = $dialog->ask($output,
            'Enter first Name [Administrator]: ',
            'Administrator'
        );
        $phone = $dialog->ask($output,
            'Enter admin phone number [+380931234567]: ',
            '+380931234567'
        );
        $password = $dialog->ask($output,
            'Enter password [admin]: ',
            'admin'
        );

        $output->writeln("");
        $output->writeln("You entered:");
        $output->writeln("e-mail is: " . $email);
        $output->writeln("first name is: " . $firstName);
        $output->writeln("phone number is: " . $phone);
        $output->writeln("password is: " . $password);
        $output->writeln("");

        /** @var QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion('it\'s ok? [y/N]: ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $admin = new AdminUser();
        $encoder = $this->getContainer()->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($admin, $password);
        $admin->setPassword($encodedPassword);

        $admin
            ->setFirstName($firstName)
            ->setEmail($email)
            ->setPhone($phone)
            ->setLastLogin(new DateTime('now'));

        $em->persist($admin);
        $em->flush();

        $output->writeln("");
        $output->writeln(" Created successfully. ");
    }
}
