<?php

namespace App\Import;

class CsvDelimiterDetector
{
    public function detect(string $content, array $candidates = [',', ';', "\t"]): string
    {
        $counts = array_fill_keys($candidates, 0);

        $lines = preg_split('/\r\n|\r|\n/', $content);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            foreach ($candidates as $candidate) {
                $counts[$candidate] += $this->countDelimiter($line, $candidate);
            }
        }

        $max = max($counts);

        if ($max === 0) {
            return ',';
        }

        if (isset($counts[','], $counts[';']) && $counts[','] === $max && $counts[';'] === $max) {
            return ',';
        }

        foreach ($candidates as $candidate) {
            if ($counts[$candidate] === $max) {
                return $candidate;
            }
        }

        return ',';
    }

    protected function countDelimiter(string $line, string $delimiter): int
    {
        $count = 0;
        $inQuotes = false;
        $length = strlen($line);

        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($char === '"') {
                $inQuotes = !$inQuotes;
                continue;
            }

            if ($char === $delimiter && !$inQuotes) {
                $count++;
            }
        }

        return $count;
    }
}
