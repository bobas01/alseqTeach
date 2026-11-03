<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Booking;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class LessonMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private CalendarInvite $calendar,
        private LoggerInterface $logger,
        private string $mailerFrom,
        private string $teacherEmail,
        private string $frontendPublicUrl,
        private string $appTimezone,
        private string $mailDumpDir,
    ) {
    }

    public function sameDayRequested(Booking $booking): void
    {
        $when = $this->when($booking);
        $this->send(
            $booking->getStudentEmail(),
            'Recebemos seu pedido de aula',
            "Olá {$booking->getStudentName()},\n\n"
            ."Seu pedido para {$when} chegou. Como é para hoje, o professor confirma o horário antes do Pix.\n\n"
            ."Você pode acompanhar por aqui:\n".$this->checkoutUrl($booking)."\n\n"
            .$this->legalFooter(),
        );

        $phone = $booking->getStudent()->getPhone() ?: 'não informado';
        $this->sendTeacher(
            "Pedido de aula para hoje — {$booking->getStudentName()} às {$this->time($booking)}",
            "Pedido para hoje:\n\n"
            ."Aluno: {$booking->getStudentName()}\n"
            ."E-mail: {$booking->getStudentEmail()}\n"
            ."WhatsApp: {$phone}\n"
            ."Horário: {$when}\n"
            .$this->intakeBlock($booking)
            ."\nConfirme ou recuse na área admin:\n".$this->adminUrl()."\n",
        );
    }

    public function futureRequested(Booking $booking): void
    {
        $phone = $booking->getStudent()->getPhone() ?: 'não informado';
        $this->sendTeacher(
            "Nova reserva — {$booking->getStudentName()} às {$this->time($booking)}",
            "Um aluno reservou um horário:\n\n"
            ."Aluno: {$booking->getStudentName()}\n"
            ."E-mail: {$booking->getStudentEmail()}\n"
            ."WhatsApp: {$phone}\n"
            ."Horário: {$this->when($booking)}\n"
            ."Valor: {$this->price($booking)} (Pix em andamento)\n"
            .$this->intakeBlock($booking)
            ."\nVeja na sua agenda:\n".$this->adminUrl()."\n",
        );
    }

    public function teacherApproved(Booking $booking): void
    {
        $this->send(
            $booking->getStudentEmail(),
            'Horário confirmado — pague '.$this->price($booking).' no Pix',
            "Olá {$booking->getStudentName()},\n\n"
            ."O professor confirmou sua disponibilidade para {$this->when($booking)}.\n\n"
            ."Para garantir a aula, pague {$this->price($booking)} no Pix:\n"
            .$this->checkoutUrl($booking)."\n\n"
            .$this->legalFooter(),
        );
    }

    public function teacherRejected(Booking $booking): void
    {
        $this->send(
            $booking->getStudentEmail(),
            'Horário indisponível',
            "Olá {$booking->getStudentName()},\n\n"
            ."O professor não pôde confirmar {$this->when($booking)}. Escolha outro horário na agenda.\n\n"
            .rtrim($this->frontendPublicUrl, '/')."/#agenda\n\n"
            .$this->legalFooter(),
        );
    }

    public function paid(Booking $booking): void
    {
        $lessonUrl = $this->lessonUrl($booking);
        $googleUrl = $this->calendar->googleUrl($booking, $lessonUrl);
        $ics = $this->calendar->ics($booking, $lessonUrl);
        $this->send(
            $booking->getStudentEmail(),
            'Aula confirmada — '.$this->when($booking),
            "Olá {$booking->getStudentName()},\n\n"
            ."Pagamento confirmado. Sua aula de francês está marcada para {$this->when($booking)}.\n\n"
            ."Entrar na aula:\n{$lessonUrl}\n\n"
            ."Adicionar ao Google Agenda:\n{$googleUrl}\n\n"
            ."O arquivo .ics em anexo também pode ir para o calendário do celular.\n\n"
            .$this->legalFooter(),
            $ics,
        );

        $copy = $this->calendar->teacherCopy($booking, $lessonUrl);
        $teacherGoogle = $this->calendar->googleUrl($booking, $lessonUrl, $copy['summary'], $copy['details']);
        $teacherIcs = $this->calendar->ics($booking, $lessonUrl, $copy['summary'], $copy['details']);
        $this->sendTeacher(
            'Aula paga — '.$booking->getStudentName().' · '.$this->when($booking),
            "Pagamento confirmado.\n\n"
            ."Aluno: {$booking->getStudentName()}\n"
            ."E-mail: {$booking->getStudentEmail()}\n"
            ."Horário: {$this->when($booking)}\n"
            ."Valor: {$this->price($booking)}\n"
            .$this->intakeBlock($booking)
            ."\nEntrar na aula:\n{$lessonUrl}\n\n"
            ."Adicionar à sua Google Agenda:\n{$teacherGoogle}\n\n"
            ."Sua agenda no site:\n".$this->adminUrl()."\n",
            $teacherIcs,
        );
    }

    public function reminder(Booking $booking): void
    {
        $lessonUrl = $this->lessonUrl($booking);
        $this->send(
            $booking->getStudentEmail(),
            'Amanhã tem aula de francês às '.$this->time($booking),
            "Olá {$booking->getStudentName()},\n\n"
            ."Lembrete: amanhã tem aula de francês às {$this->time($booking)} ({$this->when($booking)}).\n\n"
            ."Entrar na aula:\n{$lessonUrl}\n\n"
            ."Até lá,\n"
            .$this->legalFooter(),
        );
    }

    public function lessonUrl(Booking $booking): string
    {
        return rtrim($this->frontendPublicUrl, '/').'/aula/'.$booking->getId();
    }

    public function checkoutUrl(Booking $booking): string
    {
        return rtrim($this->frontendPublicUrl, '/').'/reserva/'.$booking->getId();
    }

    public function adminUrl(): string
    {
        return rtrim($this->frontendPublicUrl, '/').'/admin';
    }

    private function sendTeacher(string $subject, string $text): void
    {
        if (trim($this->teacherEmail) === '') {
            return;
        }
        $this->send($this->teacherEmail, $subject, $text);
    }

    private function send(string $to, string $subject, string $text, ?string $ics = null): void
    {
        try {
            $email = (new Email())
                ->from(Address::create($this->mailerFrom))
                ->to($to)
                ->subject($subject)
                ->text($text)
                ->html($this->html($subject, $text));
            if ($ics !== null) {
                $email->attach($ics, 'aula-alseq-teach.ics', 'text/calendar');
            }
            $this->mailer->send($email);
            $this->dump($to, $subject, $text, $ics);
        } catch (\Throwable $e) {
            $this->logger->error('Falha ao enviar e-mail de aula.', [
                'to' => $to,
                'subject' => $subject,
                'exception' => $e,
            ]);
            $this->dump($to, $subject, $text, $ics);
        }
    }

    private function dump(string $to, string $subject, string $text, ?string $ics): void
    {
        try {
            if (!is_dir($this->mailDumpDir) && !mkdir($this->mailDumpDir, 0775, true) && !is_dir($this->mailDumpDir)) {
                return;
            }
            $stamp = (new \DateTimeImmutable())->format('Ymd-His-u');
            $safe = preg_replace('/[^a-z0-9]+/i', '-', $subject) ?: 'email';
            $body = "To: {$to}\nSubject: {$subject}\n\n{$text}";
            if ($ics !== null) {
                $body .= "\n\n----- ICS -----\n".$ics;
            }
            file_put_contents($this->mailDumpDir.DIRECTORY_SEPARATOR.$stamp.'-'.$safe.'.txt', $body);
        } catch (\Throwable) {
            // Une copie locale ratée ne doit pas casser la réservation.
        }
    }

    private function html(string $subject, string $text): string
    {
        $body = nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

        return '<p style="font-family:Manrope,system-ui,sans-serif;color:#1c2430"><strong>'
            .htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            .'</strong></p><p style="font-family:Manrope,system-ui,sans-serif;color:#1c2430;white-space:pre-wrap">'
            .$body
            .'</p>';
    }

    private function when(Booking $booking): string
    {
        $local = $booking->getSlot()->getStartsAt()->setTimezone(new \DateTimeZone($this->appTimezone));

        return $local->format('d/m/Y').' às '.$local->format('H:i');
    }

    private function time(Booking $booking): string
    {
        return $booking->getSlot()->getStartsAt()->setTimezone(new \DateTimeZone($this->appTimezone))->format('H:i');
    }

    private function legalFooter(): string
    {
        $base = rtrim($this->frontendPublicUrl, '/');

        return "Cancelar ou exercer o direito de arrependimento (7 dias, se a aula ainda não ocorreu): {$this->teacherEmail}\n"
            ."Termos, privacidade e cancelamento: {$base}/termos\n\n"
            ."Alseq Teach\n";
    }

    private function price(Booking $booking): string
    {
        return 'R$ '.number_format($booking->getAmountCentavos() / 100, 2, ',', '.');
    }

    private function intakeBlock(Booking $booking): string
    {
        $level = LessonIntake::levelLabel($booking->getFrenchLevel());
        $goals = LessonIntake::goalLabels($booking->getConversationGoals());
        $note = $booking->getIntakeNote();
        if ($level === null && $goals === [] && $note === null) {
            return '';
        }

        $lines = [$booking->isFirstLesson() ? "\nPrimeira aula:" : "\nO que o aluno quer trabalhar:"];
        if ($level !== null) {
            $lines[] = 'Nível: '.$level;
        }
        if ($goals !== []) {
            $lines[] = 'Conversação: '.implode('; ', $goals);
        }
        if ($note !== null) {
            $lines[] = 'Recado: '.$note;
        }

        return implode("\n", $lines)."\n";
    }
}
