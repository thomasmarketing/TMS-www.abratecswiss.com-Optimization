<?php
defined('ABSPATH') || exit;

/**
 * Minimal, dependency-free XLSX reader for the sitemap use case.
 *
 * Security properties:
 * - Only reads ZIP/XML members; never executes workbook content.
 * - XML is parsed with LIBXML_NONET.
 * - External entities are not substituted.
 * - Workbook relationships are resolved rather than trusting worksheet filenames.
 */
final class TMS_WBT_XLSX_Reader {
    private string $file;
    private array $zip_members = [];
    private array $shared_strings = [];
    private array $relationships = [];

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function sheet_names(): array {
        $this->load_workbook();
        return array_keys($this->relationships);
    }

    public function find_sitemap_sheet(): array {
        $this->load_workbook();

        $matches = [];
        foreach ($this->relationships as $name => $target) {
            $normalized = $this->normalize_sheet_name($name);
            if (str_contains($normalized, 'sitemap') || str_contains($normalized, 'site map')) {
                $matches[$name] = $target;
            }
        }

        if (count($matches) === 0) {
            throw new RuntimeException('No worksheet name containing "Sitemap" or "Site Map" was found.');
        }
        if (count($matches) > 1) {
            throw new RuntimeException(
                'Multiple sitemap worksheets were found: ' . implode(', ', array_keys($matches)) .
                '. Rename/remove the ambiguous sheets so exactly one sitemap worksheet remains.'
            );
        }

        $name = array_key_first($matches);
        return ['name' => $name, 'target' => $matches[$name]];
    }

    public function read_sitemap_rows(): array {
        $sheet = $this->find_sitemap_sheet();
        $xml = $this->read_member($sheet['target']);
        $reader = $this->xml_reader($xml);

        $rows = [];
        $row = null;
        $cell = null;
        $cell_type = '';
        $cell_ref = '';
        $cell_value = '';
        $inside_cell = false;
        $inside_value = false;
        $inside_inline_text = false;

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                switch ($reader->localName) {
                    case 'row':
                        $row = [];
                        break;

                    case 'c':
                        $inside_cell = true;
                        $cell_ref = (string) $reader->getAttribute('r');
                        $cell_type = (string) $reader->getAttribute('t');
                        $cell_value = '';
                        break;

                    case 'v':
                        if ($inside_cell) {
                            $inside_value = true;
                        }
                        break;

                    case 't':
                        if ($inside_cell) {
                            $inside_inline_text = true;
                        }
                        break;
                }
                continue;
            }

            if ($reader->nodeType === XMLReader::TEXT || $reader->nodeType === XMLReader::CDATA) {
                if ($inside_cell && ($inside_value || $inside_inline_text)) {
                    $cell_value .= (string) $reader->value;
                }
                continue;
            }

            if ($reader->nodeType === XMLReader::END_ELEMENT) {
                switch ($reader->localName) {
                    case 'v':
                        $inside_value = false;
                        break;

                    case 't':
                        $inside_inline_text = false;
                        break;

                    case 'c':
                        if ($inside_cell && is_array($row)) {
                            $col = preg_replace('/\d+/', '', $cell_ref);

                            if ($col !== '') {
                                $value = $cell_value;

                                if ($cell_type === 's' && $value !== '') {
                                    $index = (int) $value;
                                    $value = $this->shared_strings[$index] ?? '';
                                }

                                // Inline strings can contain multiple text runs.
                                $row[$col] = $value;
                            }
                        }

                        $inside_cell = false;
                        $inside_value = false;
                        $inside_inline_text = false;
                        $cell_ref = '';
                        $cell_type = '';
                        $cell_value = '';
                        break;

                    case 'row':
                        if (is_array($row)) {
                            $rows[] = $row;
                        }
                        $row = null;
                        break;
                }
            }
        }

        $reader->close();

        return $rows;
    }

    private function load_workbook(): void {
        if ($this->relationships) {
            return;
        }

        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive is required to read XLSX files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->file) !== true) {
            throw new RuntimeException('The uploaded file is not a readable XLSX archive.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!empty($stat['name'])) {
                $this->zip_members[$stat['name']] = true;
            }
        }

        $this->zip = $zip;

        if (isset($this->zip_members['xl/sharedStrings.xml'])) {
            $this->shared_strings = $this->parse_shared_strings(
                $this->read_member('xl/sharedStrings.xml')
            );
        }

        $workbook = $this->xml_reader($this->read_member('xl/workbook.xml'));
        $rels = $this->parse_relationships($this->read_member('xl/_rels/workbook.xml.rels'));

        while ($workbook->read()) {
            if ($workbook->nodeType === XMLReader::ELEMENT && $workbook->localName === 'sheet') {
                $name = (string) $workbook->getAttribute('name');
                $rid = (string) $workbook->getAttribute('r:id');
                if ($name !== '' && isset($rels[$rid])) {
                    $this->relationships[$name] = $this->normalize_target($rels[$rid]);
                }
            }
        }
        $workbook->close();
    }

    private function parse_relationships(string $xml): array {
        $reader = $this->xml_reader($xml);
        $result = [];
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'Relationship') {
                $id = (string) $reader->getAttribute('Id');
                $target = (string) $reader->getAttribute('Target');
                if ($id !== '' && $target !== '') {
                    $result[$id] = $target;
                }
            }
        }
        $reader->close();
        return $result;
    }

    private function parse_shared_strings(string $xml): array {
        $reader = $this->xml_reader($xml);
        $strings = [];
        $current = '';
        $inside_si = false;

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'si') {
                $inside_si = true;
                $current = '';
            } elseif ($inside_si && $reader->nodeType === XMLReader::ELEMENT && $reader->localName === 't') {
                $reader->read();
                $current .= (string) $reader->value;
            } elseif ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'si') {
                $strings[] = $current;
                $inside_si = false;
            }
        }
        $reader->close();
        return $strings;
    }

    private function normalize_target(string $target): string {
        $target = ltrim(str_replace('\\', '/', $target), '/');
        if (str_starts_with($target, 'worksheets/')) {
            return 'xl/' . $target;
        }
        return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
    }

    private function read_member(string $name): string {
        if (!isset($this->zip_members[$name])) {
            throw new RuntimeException('Required XLSX member is missing: ' . $name);
        }
        $data = $this->zip->getFromName($name);
        if ($data === false) {
            throw new RuntimeException('Unable to read XLSX member: ' . $name);
        }
        if (strlen($data) > 20 * 1024 * 1024) {
            throw new RuntimeException('XLSX XML member is too large: ' . $name);
        }
        return $data;
    }

    private function xml_reader(string $xml): XMLReader {
        $reader = new XMLReader();
        if (!$reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new RuntimeException('Invalid XML inside the XLSX workbook.');
        }
        return $reader;
    }

    private function normalize_sheet_name(string $name): string {
        $name = preg_replace('/\s+/u', ' ', trim($name));
        return strtolower($name);
    }
}
