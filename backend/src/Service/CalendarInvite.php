<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Booking;

final class CalendarInvite
{
    public function ics(Booking $booking, string $lessonUrl, ?string $summary = null, ?string $details = null): string
    {
        $uid = $booking->getId().'@alseqteach';
        $stamp = $this->utcStamp(new \DateTimeImmutable());
        $start = $this->utcStamp($booking->getSlot()->getStartsAt());
        $end = $this->utcStamp($booking->getSlot()->getEndsAt());
        $summary = $this->escape($summary ?? 'Aula de francês — Alseq Teach');
        $description = $this->escape($details ?? ("Aula particular de francês.\nEntrar na aula: ".$lessonUrl));

        return "BEGIN:VCALENDAR\r\n"
            ."VERSION:2.0\r\n"
            ."PRODID:-//Alseq Teach//Aulas//PT\r\n"
            ."CALSCALE:GREGORIAN\r\n"
            ."METHOD:PUBLISH\r\n"
            ."BEGIN:VEVENT\r\n"
            ."UID:$uid\r\n"
            ."DTSTAMP:$stamp\r\n"
            ."DTSTART:$start\r\n"
            ."DTEND:$end\r\n"
            ."SUMMARY:$summary\r\n"
            ."DESCRIPTION:$description\r\n"
            ."URL:$lessonUrl\r\n"
            ."END:VEVENT\r\n"
            ."END:VCALENDAR\r\n";
    }

    public function googleUrl(Booking $booking, string $lessonUrl, ?string $summary = null, ?string $details = null): string
    {
        $dates = $this->utcStamp($booking->getSlot()->getStartsAt())
            .'/'.$this->utcStamp($booking->getSlot()->getEndsAt());

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $summary ?? 'Aula de francês — Alseq Teach',
            'dates' => $dates,
            'details' => $details ?? ("Aula particular de francês.\nEntrar na aula: ".$lessonUrl),
            'location' => $lessonUrl,
        ]);
    }

    /**
     * @return array{summary: string, details: string}
     */
    public function teacherCopy(Booking $booking, string $lessonUrl): array
    {
        $phone = $booking->getStudent()->getPhone() ?: 'não informado';

        return [
            'summary' => 'Aula com '.$booking->getStudentName().' — Alseq Teach',
            'details' => "Aluno: {$booking->getStudentName()}\n"
                ."E-mail: {$booking->getStudentEmail()}\n"
                ."WhatsApp: {$phone}\n"
                ."Entrar na aula: {$lessonUrl}",
        ];
    }

    private function utcStamp(\DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
    }

    private function escape(string $value): string
    {
        return str_replace(["\\", ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], $value);
    }
}
