<?php

namespace Database\Seeders;

use App\Enums\ElectiveStatus;
use App\Enums\ExamType;
use App\Enums\Language;
use App\Enums\Season;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AwpfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semester = Semester::query()->firstOrCreate(
            ['year' => 2025, 'season' => Season::Winter]
        );

        $courses = $this->getCourses();

        $this->command->info('Seeding '.count($courses).' AWPF courses...');

        foreach ($courses as $index => $course) {
            try {
                $professorId = null;

                if (! empty($course['lecturer'])) {
                    $professor = $this->createOrFindProfessor($course['lecturer']);

                    if ($professor) {
                        $professorId = $professor->id;
                    }
                }

                $awpf = Awpf::query()->create([
                    'name' => $course['name'],
                    'content' => $course['content'] ?? null,
                    'goals' => $course['goals'] ?? null,
                    'literature' => $course['literature'] ?? null,
                    'credits' => $course['credits'],
                    'max_participants' => $course['max_participants'] ?? null,
                    'hours_per_week' => $course['hours_per_week'] ?? null,
                    'type_of_class' => $course['type_of_class'] ?? null,
                    'language' => $course['language'],
                    'exam_type' => $course['exam_type'],
                    'status' => ElectiveStatus::Published,
                    'professor_id' => $professorId,
                    'lecturer_name' => $course['lecturer'] ?? null,
                    'course_url' => 'https://fang.thws.de/fakultaet/awpf/faecheruebersichtanmeldungstornierung/',
                ]);

                $awpf->assignToSemester($semester);
                $this->createSchedules($awpf, $index, $course['hours_per_week'] ?? 2.0);

                $this->command->info("✓ Seeded: {$awpf->name}");
            } catch (\Exception $e) {
                $this->command->error("Failed to seed course '{$course['name']}': ".$e->getMessage());
            }
        }

        $this->command->info('AWPF seeding completed!');
    }

    /**
     * @return array<int, array{name: string, credits: float, language: Language, exam_type: ExamType, lecturer: string, content?: string, goals?: string, literature?: string, max_participants?: int, hours_per_week?: float, type_of_class?: string}>
     */
    protected function getCourses(): array
    {
        return [
            // 1. Languages — Würzburg
            [
                'name' => 'Arabisch A1a',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Alsihamat',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Dieser Kurs richtet sich an absolute Anfänger ohne Vorkenntnisse. Die Studierenden erwerben grundlegende Kenntnisse des modernen Hocharabisch: Alphabet, Aussprache, einfache Satzstrukturen und Alltagswortschatz. Übungen zur Lese- und Hörfähigkeit ergänzen den Unterricht.',
                'goals' => 'Die Studierenden können einfache Alltagssituationen auf Arabisch bewältigen, das Alphabet lesen und schreiben sowie grundlegende Grammatikstrukturen anwenden.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'English for Computer Science (B2)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Wassermann',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'This course targets computer science and IT students at B2 level. It focuses on discipline-specific texts and vocabulary alongside professional communication skills such as writing technical emails, giving presentations, and summarizing research abstracts.',
                'goals' => 'Students will improve their academic and professional English with a focus on computer science terminology, technical writing, and verbal communication in professional contexts.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'English for Healthcare Management (B2)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Wassermann',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'This course is designed for healthcare management students at B2 level. It covers specialized medical and management vocabulary, professional written and spoken communication, and practice with typical workplace scenarios in international healthcare settings. Prior English knowledge of at least six years is required.',
                'goals' => 'Students will expand their command of healthcare English, communicate confidently in professional settings, and produce clear written reports and presentations.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Intensive Spoken English (C1)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Oral,
                'lecturer' => 'Wassermann',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'This advanced course trains speaking and listening comprehension at C1 level. Students simulate professional conversations, participate in debates and negotiations, give subject-specific presentations, and work with authentic audio and video recordings from academic and workplace contexts.',
                'goals' => 'Students will communicate fluently in academic and professional English, lead discussions, give presentations confidently, and understand complex spoken material.',
                'literature' => 'Authentic audio/video materials provided in class.',
            ],
            [
                'name' => 'English Refresher Course (B1)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Echeverry',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'This interdisciplinary course consolidates foundational English knowledge through grammar revision, vocabulary building, and comprehension exercises. It is aimed at students with at least three years of school-level English who need to refresh their skills before entering more advanced language courses.',
                'goals' => 'Students will solidify core English grammar and vocabulary, improving reading and listening comprehension in general contexts.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Italienisch A1a',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Akács',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Dieser Kurs richtet sich an absolute Anfänger ohne Vorkenntnisse. Es werden die ersten fünf Lektionen des Lehrwerks behandelt. Wortschatz und Hausaufgaben sind zwischen den Stunden eigenständig zu erarbeiten. Der erfolgreiche Abschluss von A1a, A1b und A2a eröffnet den Weg zum UNIcert® Basiszertifikat.',
                'goals' => 'Die Studierenden beherrschen grundlegende Alltagsphrasen und einfache Grammatikstrukturen des Italienischen und können sich in vertrauten Situationen verständigen.',
                'literature' => 'Allegro nuovo A1 Kurs- und Übungsbuch (Klett-Verlag, ISBN 978-3-12-525590-6).',
            ],
            [
                'name' => 'Japanisch A1a',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Iwawaki-Riebel',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'Der Kurs führt absolute Anfänger in die japanische Sprache ein. Schwerpunkte sind die Phonetik, das Hiragana-Schriftsystem sowie grundlegende Grammatik und Alltagskommunikation. Die Lektionen 1–6 des Lehrwerks werden behandelt.',
                'goals' => 'Die Studierenden können einfache Alltagsgespräche auf Japanisch führen, Hiragana lesen und schreiben und verfügen über einen grundlegenden Wortschatz.',
                'literature' => 'Japanisch im Sauseschritt 1 (Lehr- und Übungsbuch für Anfänger).',
            ],
            [
                'name' => 'Spanisch A1a',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Mesenberg-Demel',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Einführungskurs Spanisch für absolute Anfänger. Es werden grundlegende Grammatikstrukturen und Wortschatz für alltägliche Sprechsituationen in Gegenwart vermittelt. Partnerübungen stehen im Mittelpunkt; Hausaufgaben werden eigenständig erarbeitet. Die Lektionen 1–5 von „Con gusto nuevo A1" werden behandelt.',
                'goals' => 'Die Studierenden können sich auf Spanisch vorstellen, einfache Alltagsgespräche führen und grundlegende Grammatik anwenden.',
                'literature' => 'Con gusto nuevo A1 Kurs- und Übungsbuch (Klett Verlag, ISBN 978-3-12-514671-6).',
            ],
            [
                'name' => 'Spanish for International Students A1a',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Kreiner-Wegener',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'An introductory Spanish course taught in English for international students with no prior knowledge. Students learn to introduce themselves, navigate everyday situations, and communicate in common contexts using lessons 1–5 of the coursebook.',
                'goals' => 'Students will acquire basic communicative competence in Spanish, including cultural and strategic knowledge for everyday situations.',
                'literature' => 'Aula Internacional 1 (course book provided).',
            ],
            [
                'name' => 'Spanisch A1b',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Belleri Caballer',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Fortsetzungskurs auf A1-Niveau. Die Lektionen 6–9 von „Con gusto nuevo A1" werden erarbeitet. Inhalte: Wegbeschreibungen, Hotelreservierungen, Tagesabläufe und Vergleiche. Grammatik: Reflexivverben, Perfekt, Gerundium und Komparativ.',
                'goals' => 'Die Studierenden können Alltagssituationen sprachlich meistern, Empfehlungen geben, Beschwerden äußern und ihren Tagesablauf beschreiben.',
                'literature' => 'Con gusto nuevo A1 Kurs- und Übungsbuch (Klett Verlag, ISBN 978-3-12-514671-6).',
            ],
            [
                'name' => 'Spanisch A2a',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Kreiner-Wegener',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Kurs auf A2-Niveau. Behandelt werden Lektionen 9 (ab S. 82), 10 und 11 aus „Con gusto nuevo A1" sowie Lektionen 1–2 aus „Con gusto nuevo A2". Schwerpunkte: Vergangenheitsformen, Zukunft mit „ir a", Relativsätze und Alltagsgespräche in Gegenwart und Vergangenheit.',
                'goals' => 'Die Studierenden können sich über Erlebnisse und Pläne äußern, Alltagsgespräche in Vergangenheit und Zukunft führen und komplexere grammatische Strukturen anwenden.',
                'literature' => 'Con gusto nuevo A1 (ISBN 978-3-12-514671-6) und Con gusto nuevo A2 (ISBN 978-3-12-514677-8), Klett Verlag.',
            ],

            // 1. Languages — Schweinfurt
            [
                'name' => 'French for International Students A1 a/b',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Eckert',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 4.0,
                'max_participants' => 25,
                'content' => 'A full-year introductory French course taught in English for international students with no prior knowledge. The course covers both A1a and A1b content, developing listening, speaking, reading and writing skills for everyday communication.',
                'goals' => 'Students will achieve A1 proficiency in French, enabling them to handle basic communication tasks and understand simple spoken and written French.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Spanisch A1 a/b',
                'credits' => 5.0,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Gomez',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 4.0,
                'max_participants' => 25,
                'content' => 'Ganzjähriger Spanischkurs für absolute Anfänger am Standort Schweinfurt. Der Kurs deckt die Inhalte von A1a und A1b ab und vermittelt grundlegende Kommunikationsfähigkeiten für Alltagssituationen.',
                'goals' => 'Die Studierenden erreichen das Sprachniveau A1 und können einfache Gespräche auf Spanisch führen sowie grundlegende Grammatik anwenden.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Effective Communication for Work and Study Abroad (B2)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Gostomski',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'This course prepares students at B2 level for professional and academic communication in international environments. Topics include job applications, interviews, academic writing, and intercultural communication strategies relevant to study abroad and international internships.',
                'goals' => 'Students will improve their ability to communicate effectively in professional and academic English contexts abroad.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'English Conversation (B2)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Gostomski',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'A conversation-focused course at B2 level designed to build fluency and confidence in spoken English. Students discuss current events, engage in structured debates, and practice a wide range of everyday and professional speaking scenarios.',
                'goals' => 'Students will speak more fluently and confidently in English across a range of everyday and professional topics.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Chinesisch A1 a/b',
                'credits' => 5.0,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Hummitzsch',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 4.0,
                'max_participants' => 20,
                'content' => 'Ganzjähriger Einführungskurs Chinesisch (Mandarin) für absolute Anfänger. Vermittelt werden Pinyin-Aussprache, Grundzüge der Zeichenschrift, grundlegende Grammatik und Alltagswortschatz. Der Kurs deckt A1a und A1b ab.',
                'goals' => 'Die Studierenden erreichen A1-Niveau in Chinesisch und können einfache Alltagssituationen sprachlich bewältigen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Chinesisch A2 a/b',
                'credits' => 5.0,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Hummitzsch',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 4.0,
                'max_participants' => 20,
                'content' => 'Aufbaukurs Chinesisch auf A2-Niveau für Studierende mit abgeschlossenem A1-Kurs. Erweiterung von Wortschatz, Grammatik und Schriftkenntnissen für komplexere Kommunikationssituationen im Alltag und im beruflichen Umfeld.',
                'goals' => 'Die Studierenden können sich auf Chinesisch über vertraute Themen unterhalten und kürzere Texte lesen und schreiben.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Japanisch A1 a (Schweinfurt)',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Iwawaki-Riebel',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'Einführungskurs Japanisch für Anfänger am Standort Schweinfurt. Schwerpunkte sind Phonetik, Hiragana-Schrift und grundlegende Alltagskommunikation.',
                'goals' => 'Die Studierenden können einfache Alltagsgespräche auf Japanisch führen und die Hiragana-Schrift lesen und schreiben.',
                'literature' => 'Japanisch im Sauseschritt 1 (Lehr- und Übungsbuch für Anfänger).',
            ],
            [
                'name' => 'Spanish for International Students A1 a/b',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Meyer-Urena',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 4.0,
                'max_participants' => 25,
                'content' => 'A full-year introductory Spanish course in English for international students. Covers A1a and A1b content: basic grammar, vocabulary for everyday situations, and communicative tasks for students new to the language.',
                'goals' => 'Students will reach A1 proficiency in Spanish and handle basic communication in everyday situations.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Kommunikationstraining auf Deutsch (B1/B2)',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Oral,
                'lecturer' => 'Schäfer',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'Dieser Kurs richtet sich an internationale Studierende mit deutschen Sprachkenntnissen auf B1/B2-Niveau. Im Mittelpunkt stehen mündliche Kommunikation, Präsentationstechniken und das Führen von Gesprächen im akademischen und beruflichen Umfeld.',
                'goals' => 'Die Studierenden verbessern ihre mündliche Ausdrucksfähigkeit auf Deutsch und können sich sicher in akademischen und beruflichen Kontexten verständigen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Intensive Spoken English (C1) Schweinfurt',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Oral,
                'lecturer' => 'Körner',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'An advanced spoken English course at C1 level for students preparing for study abroad or international internships. Students practise presentations, debates, negotiations and professional conversations using authentic audio and video materials.',
                'goals' => 'Students will communicate fluently in academic and professional contexts, present complex topics confidently, and comprehend authentic spoken English.',
                'literature' => 'Authentic audio/video materials provided in class.',
            ],

            // 2. Cultural Studies — Würzburg
            [
                'name' => 'Einführung in die mittelhochdeutsche Sprache und Kultur',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Dunphy',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'Dieser Kurs gibt eine Einführung in das Mittelhochdeutsche – die südliche Variante des Deutschen im Mittelalter. Studierende lesen Texte von Walther von der Vogelweide, Hartmann von Aue und anderen mittelalterlichen Autoren und erkunden die höfische und städtische Kultur des Mittelalters.',
                'goals' => 'Die Studierenden können mittelhochdeutsche Texte lesen und verstehen und kennen die wichtigsten kulturellen und sprachlichen Besonderheiten des Mittelalters.',
                'literature' => 'Textmaterialien werden im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Introduction to British and Irish Cultures',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Dunphy',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'This course examines Britain and Ireland as English-speaking European nations. Topics include political and legal structures, religious traditions, history, and cultural life. The course also serves as an opportunity to practise spoken English at B2 level.',
                'goals' => 'Students will gain broad knowledge of British and Irish society, culture and institutions, and improve their spoken English proficiency.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Philosophie des Abendlandes',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Hübner',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 60,
                'content' => 'Die Lehrveranstaltung verfolgt die Entwicklung des abendländischen Denkens von der Antike bis zur Gegenwart. Anhand bedeutender Denker – von Platon über Kant bis Nietzsche – werden die zentralen Fragen der Philosophie zu Erkenntnis, Moral, Freiheit und dem Wesen des Menschen untersucht.',
                'goals' => 'Die Studierenden kennen die wichtigsten Epochen und Vertreter der westlichen Philosophie und können philosophische Argumente nachvollziehen und kritisch reflektieren.',
                'literature' => 'Einführung in die Philosophie (Reclam); weitere Lektürehinweise werden im Kurs gegeben.',
            ],

            // 3. Natural Sciences & Technology — Würzburg
            [
                'name' => 'Einführung in Excel und Visual Basic',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Latz',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'Der Kurs führt in Microsoft Excel als Tabellenkalkulationsprogramm mit umfangreichen mathematischen Funktionen ein: Statistik, Trigonometrie und technische Anwendungen. Ergänzend wird die Automatisierung von Abläufen durch Visual-Basic-Makros behandelt, mit Anwendungen in kaufmännischen und wissenschaftlichen Bereichen.',
                'goals' => 'Die Studierenden können Excel für technische und wissenschaftliche Aufgaben einsetzen und einfache VBA-Makros erstellen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Programmieren lernen mit Python und ChatGPT',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Stilgenbauer',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'Moderner Einstieg in die Programmierung mit Python, unterstützt durch KI-Assistenz via ChatGPT. Python eignet sich durch seine klare Syntax hervorragend für Einsteiger aus allen Fachrichtungen. Anwendungsgebiete: Datenanalyse, Automatisierung, wissenschaftliches Rechnen. Der Unterricht findet überwiegend in Präsenz statt (Bring Your Own Device).',
                'goals' => 'Die Studierenden beherrschen grundlegende Python-Konzepte und können einfache Programme für ihren Studienschwerpunkt entwickeln.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt. Python (kostenlos unter python.org).',
            ],

            // 3. Natural Sciences & Technology — Schweinfurt
            [
                'name' => 'Integration Bee',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Bittner',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'An engaging mathematics course structured as a competition-style event. Students practise a wide variety of integration techniques including substitution, partial fractions, and integration by parts through increasingly challenging problems, culminating in a friendly integration contest.',
                'goals' => 'Students will strengthen their integral calculus skills and develop problem-solving speed and accuracy.',
                'literature' => 'Problem sets provided by the instructor.',
            ],
            [
                'name' => 'Light and Radiation Measurement Technology',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Hartmann',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'This course covers the fundamentals of photometric and radiometric measurement, including light sources, detectors, and measurement systems. Students learn to apply measurement standards and interpret measurement results in engineering contexts.',
                'goals' => 'Students will understand the principles of light and radiation measurement and apply them in practical engineering scenarios.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Technical Temperature Measurement',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Hartmann',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'An overview of temperature measurement techniques used in industrial and scientific applications. Topics include thermocouples, resistance thermometers, pyrometers, and calibration methods. The course includes practical examples from engineering and process technology.',
                'goals' => 'Students will select and apply appropriate temperature measurement methods for technical problems and understand the principles behind each sensor type.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Datenbank - Abfragen mit SQL',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Meier',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Einführung in relationale Datenbanken und die Abfragesprache SQL. Inhalte: Datenbankmodellierung, grundlegende und fortgeschrittene SELECT-Abfragen, Joins, Unterabfragen sowie Datenmanipulation. Der Kurs ist praxisorientiert mit zahlreichen Übungsaufgaben.',
                'goals' => 'Die Studierenden können Datenbanken modellieren und SQL-Abfragen für praktische Datenanalyseaufgaben formulieren.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'LaTeX - Erstellen wissenschaftlicher Texte',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Meier',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Einführung in das Textsatzsystem LaTeX für das Erstellen wissenschaftlicher Dokumente. Behandelt werden Dokumentstruktur, mathematische Formeln, Abbildungen, Tabellen, Literaturverwaltung mit BibTeX und das Erstellen von Präsentationen mit Beamer.',
                'goals' => 'Die Studierenden können professionelle wissenschaftliche Dokumente, Berichte und Präsentationen mit LaTeX erstellen.',
                'literature' => 'LaTeX – Einführung (Lehmann / Klix, Hanser Verlag); LaTeX (kostenlos unter latex-project.org).',
            ],
            [
                'name' => 'Technischer Vertrieb',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Rieß',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 40,
                'content' => 'Dieser Kurs vermittelt Grundlagen des technischen Vertriebs für Ingenieure und Naturwissenschaftler. Inhalte: Vertriebsprozesse, Kundenkommunikation, technische Beratung, Angebotserstellung und Vertragsverhandlung in technischen Branchen.',
                'goals' => 'Die Studierenden verstehen die Besonderheiten des technischen Vertriebs und können grundlegende Vertriebs- und Kommunikationstechniken anwenden.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Wärmenetze und Gebäudeintegration',
                'credits' => 5.0,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Schicktanz',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 4.0,
                'max_participants' => 40,
                'content' => 'Umfassender Kurs zu Planung, Betrieb und Integration von Wärmenetzen in Gebäude und Quartiere. Behandelt werden Wärmeversorgungssysteme, Niedertemperaturnetze, Wärmepumpen, solare Einspeisung und die energetische Sanierung im Bestand.',
                'goals' => 'Die Studierenden können Wärmenetze planen, dimensionieren und in bestehende Gebäudestrukturen integrieren.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Fundamentals of Biomedical Engineering',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Schnettler',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 40,
                'content' => 'An introduction to the interdisciplinary field of biomedical engineering. Topics include biomechanics, biosensors, medical imaging, biomaterials, and the regulatory context of medical devices. The course bridges engineering principles with life sciences applications.',
                'goals' => 'Students will understand core biomedical engineering concepts and their application in modern medical technology.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Creating Professional Scientific Documents with LaTeX',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Storath',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'A hands-on introduction to LaTeX for producing professional scientific documents in English. Topics include document structure, mathematical typesetting, figures, tables, bibliography management with BibTeX, and creating slide presentations with Beamer.',
                'goals' => 'Students will produce high-quality scientific reports, theses, and presentations using LaTeX.',
                'literature' => 'LaTeX documentation available at latex-project.org; course materials provided.',
            ],
            [
                'name' => 'Introduction to Programming in Python',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Wahyudi',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 30,
                'content' => 'A beginner-friendly introduction to Python programming. The course covers variables, data types, control flow, functions, and basic data structures. Students apply their knowledge through practical exercises drawn from scientific and engineering contexts.',
                'goals' => 'Students will write simple Python programs, understand programming fundamentals, and apply coding skills to practical problems.',
                'literature' => 'Python documentation (docs.python.org); course materials provided.',
            ],
            [
                'name' => 'Abfallwirtschaft',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Walter',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 40,
                'content' => 'Überblick über die kommunale und industrielle Abfallwirtschaft in Deutschland und Europa. Inhalte: Abfallhierarchie, Kreislaufwirtschaft, Recyclingverfahren, thermische Verwertung, rechtliche Grundlagen und aktuelle Herausforderungen der Ressourcenschonung.',
                'goals' => 'Die Studierenden verstehen die Grundprinzipien der Kreislaufwirtschaft und kennen relevante Entsorgungsverfahren und gesetzliche Rahmenbedingungen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],

            // 4. History, Politics, Law, Economics — Würzburg
            [
                'name' => 'Korruption in Deutschland',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Dolata',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 40,
                'content' => 'Analyse von Korruption in Wirtschaft, Politik und Verwaltung in Deutschland. Behandelt werden Erscheinungsformen, Ursachen und Auswirkungen von Korruption sowie Instrumente der Korruptionsprävention und strafrechtliche Konsequenzen.',
                'goals' => 'Die Studierenden kennen die wichtigsten Erscheinungsformen von Korruption, können einschlägige Fälle analysieren und kennen präventive und rechtliche Gegenmaßnahmen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Gründen@THWS',
                'credits' => 5.0,
                'language' => Language::German,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Waschik',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 4.0,
                'max_participants' => 30,
                'content' => 'Praxisorientierter Kurs zur unternehmerischen Ideenfindung und Gründungsvorbereitung. Studierende entwickeln eigene Geschäftsideen, erarbeiten Business-Model-Canvas und Pitches und erhalten Einblick in Finanzierung, Rechtsfragen und Marktanalyse. Teamarbeit und externe Gastreferenten sind zentraler Bestandteil.',
                'goals' => 'Die Studierenden können eine Geschäftsidee entwickeln, ein Business Model Canvas erstellen und ihr Vorhaben professionell präsentieren.',
                'literature' => 'Osterwalder/Pigneur: Business Model Generation; weitere Materialien im Kurs.',
            ],
            [
                'name' => 'Staat und Verwaltung in Deutschland',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Bohl',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 50,
                'content' => 'Einführung in das deutsche Staats- und Verwaltungsrecht. Inhalte: Staatsaufbau, Gewaltenteilung, Grundrechte, Bundesländer und Kommunen, Verwaltungsstrukturen sowie aktuelle Reformthemen der öffentlichen Verwaltung.',
                'goals' => 'Die Studierenden verstehen den Aufbau des deutschen Staates und der öffentlichen Verwaltung und können grundlegende Rechtsbegriffe einordnen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],

            // 4. History, Politics, Law, Economics — Schweinfurt
            [
                'name' => 'Der Kalte Krieg',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Hawlitschek',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 40,
                'content' => 'Überblick über die Geschichte des Kalten Krieges von 1947 bis 1991. Behandelt werden Entstehung, wichtige Krisen (Korea, Kuba, Vietnam), ideologische Konfrontation, Rüstungswettlauf, die Rolle Deutschlands und das Ende des Ost-West-Konflikts.',
                'goals' => 'Die Studierenden kennen die zentralen Ereignisse und Dynamiken des Kalten Krieges und können deren Bedeutung für die heutige Weltpolitik reflektieren.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Nachhaltige Entwicklung von Wohnimmobilien im Bestand',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Bittner',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 35,
                'content' => 'Der Kurs behandelt Strategien zur nachhaltigen Entwicklung und energetischen Sanierung von Wohngebäuden im Bestand. Inhalte: Energieeffizienz, Förderprogramme, Lebenszyklusbetrachtung, Klimaschutz und wirtschaftliche Bewertung von Sanierungsmaßnahmen.',
                'goals' => 'Die Studierenden können Sanierungsstrategien bewerten, Fördermöglichkeiten identifizieren und Nachhaltigkeitsaspekte in immobilienwirtschaftliche Entscheidungen einbeziehen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],

            // 5. Pedagogy, Psychology, Social Sciences, Soft Skills — Würzburg
            [
                'name' => 'Was ist Freiheit, Recht, Gerechtigkeit?',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Stickler',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 40,
                'content' => 'Seminaristischer Überblick über grundlegende Fragen der Rechts- und Sozialphilosophie: Was bedeutet Freiheit? Wie entsteht Recht? Was ist eine gerechte Gesellschaft? Anhand klassischer und zeitgenössischer Texte werden diese Fragen diskutiert und auf aktuelle gesellschaftliche Probleme angewendet.',
                'goals' => 'Die Studierenden können zentrale Begriffe der Rechts- und Sozialphilosophie erläutern und auf aktuelle gesellschaftliche Fragestellungen anwenden.',
                'literature' => 'Ausgewählte Texte werden im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Klinische Psychologie und kommunikative Grundlagen',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Renz',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 35,
                'content' => 'Einführung in ausgewählte Themen der klinischen Psychologie mit einem Fokus auf kommunikative Grundlagen für professionelle Kontexte. Inhalte: psychische Störungsbilder, Grundlagen der psychologischen Gesprächsführung, Empathie und Gesprächstechniken für Gesundheits- und Sozialberufe.',
                'goals' => 'Die Studierenden kennen grundlegende klinisch-psychologische Konzepte und können professionelle Kommunikationstechniken in helfenden Berufen anwenden.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],

            // 5. Pedagogy, Psychology, Social Sciences, Soft Skills — Schweinfurt
            [
                'name' => 'Service-Learning: Connect - Involve - Reflect',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Oral,
                'lecturer' => 'Aicha',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'This course combines academic learning with community engagement. Students connect with local organisations, carry out a service project, and reflect on their experience in structured discussions. The course develops civic responsibility, teamwork, and intercultural competence.',
                'goals' => 'Students will engage meaningfully with the community, develop practical project skills, and critically reflect on their service experience.',
                'literature' => 'Course materials provided by the instructor.',
            ],
            [
                'name' => 'Leadership Training (B2/C1)',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Schäfer',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 20,
                'content' => 'This course develops leadership and professional communication skills in English at B2/C1 level. Topics include leadership styles, team dynamics, conflict resolution, giving and receiving feedback, and motivating others in international work environments.',
                'goals' => 'Students will apply leadership concepts and improve their professional English communication for managing teams and projects in global contexts.',
                'literature' => 'Course materials provided by the instructor.',
            ],

            // 6. Creativity, Art, Literature — Würzburg
            [
                'name' => 'Grundlagen in digitalem Design',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Groß',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Einführung in grundlegende Konzepte und Werkzeuge des digitalen Designs. Themen: Gestaltprinzipien, Typografie, Farbtheorie, Layout und der Umgang mit gängigen Design-Tools. Eigenes Notebook erforderlich (Bring Your Own Device).',
                'goals' => 'Die Studierenden kennen grundlegende Designprinzipien und können digitale Medien ansprechend gestalten.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Brettspiele - Forschung und Praxiseinsatz',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Jüngst',
                'type_of_class' => 'Seminar',
                'hours_per_week' => 2.0,
                'max_participants' => 25,
                'content' => 'Interdisziplinäres Seminar zu Brettspielen aus wissenschaftlicher und pädagogischer Perspektive. Behandelt werden Spielforschung, Game-Design-Grundlagen, der Einsatz von Brettspielen in Bildungskontexten sowie praktische Spielerprobung und -analyse.',
                'goals' => 'Die Studierenden kennen den Forschungsstand zu analogen Spielen und können Brettspiele kritisch analysieren sowie in Bildungs- und Freizeitkontexten einsetzen.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Medienkunde',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Moritz',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 50,
                'content' => 'Überblick über Geschichte, Strukturen und Wirkungen der modernen Medienlandschaft. Behandelt werden Printmedien, Rundfunk, digitale Medien und soziale Netzwerke sowie medienethische Fragen, Informationskompetenz und Medienkritik.',
                'goals' => 'Die Studierenden verstehen die Funktionsweise und gesellschaftliche Bedeutung verschiedener Medienformen und können Medieninhalte kritisch reflektieren.',
                'literature' => 'Lehrmaterial wird im Kurs bereitgestellt.',
            ],
            [
                'name' => 'Musik in Film, Fernsehen, Radio und Internet',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Moritz',
                'type_of_class' => 'Vorlesung',
                'hours_per_week' => 2.0,
                'max_participants' => 50,
                'content' => 'Der Kurs analysiert die Funktion und Wirkung von Musik in verschiedenen Medien: Filmmusik, Serienthemen, Werbemusik, Radioformate und Streaming-Plattformen. Musikalische Mittel werden im medialen Kontext besprochen und durch Hörbeispiele veranschaulicht.',
                'goals' => 'Die Studierenden können den Einsatz von Musik in Medienproduktionen analysieren und die Wechselwirkung zwischen Musik und Bild beschreiben.',
                'literature' => 'Lehrmaterial und Hörbeispiele werden im Kurs bereitgestellt.',
            ],

            // 7. VHB Online Courses
            [
                'name' => 'Internetkompetenz: Intranet Grundlagen',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Nacke',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der Virtuellen Hochschule Bayern (vhb). Grundlagen vernetzter Systeme im Unternehmenskontext: Intranet-Architektur, Netzwerkprotokolle, Sicherheitskonzepte und kollaborative Plattformen. Selbststudium mit begleitenden Tests.',
                'goals' => 'Die Studierenden verstehen Aufbau und Funktionsweise von Intranet-Systemen und können grundlegende Netzwerkkonzepte erläutern.',
            ],
            [
                'name' => 'Internetkompetenz: Webdesign 1',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Nacke',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb. Einführung in HTML und CSS: Seitenstruktur, Textformatierung, Links, Bilder und grundlegende Layouttechniken. Praktische Übungen begleiten jede Einheit.',
                'goals' => 'Die Studierenden können einfache statische Webseiten mit HTML und CSS erstellen.',
            ],
            [
                'name' => 'Internetkompetenz: Webdesign 2',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Nacke',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Aufbaukurs Webdesign der vhb. Vertiefung in CSS-Layouts (Flexbox, Grid), Responsive Design und eine Einführung in JavaScript für interaktive Webseiten.',
                'goals' => 'Die Studierenden können responsiv gestaltete Webseiten erstellen und einfache Interaktionen mit JavaScript implementieren.',
            ],
            [
                'name' => 'Internetkompetenz: Webdesign 3',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Nacke',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Fortgeschrittener Webdesign-Kurs der vhb. Themen: JavaScript-Frameworks, Formularvalidierung, Barrierefreiheit und Performance-Optimierung. Abschlussprojekt: Entwicklung einer vollständigen Webpräsenz.',
                'goals' => 'Die Studierenden können komplexere Webprojekte konzipieren und umsetzen und beachten dabei Barrierefreiheit und Performance.',
            ],
            [
                'name' => 'Einführung in die Rechtswissenschaft',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Kudlich',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb. Überblick über das deutsche Rechtssystem: Verfassungsrecht, Zivilrecht, Strafrecht und öffentliches Recht. Juristische Methodik und Fallanalyse werden praxisnah erläutert.',
                'goals' => 'Die Studierenden kennen die Grundstruktur des deutschen Rechtssystems und können einfache Rechtsfragen einordnen.',
                'literature' => 'Lehrmaterial wird im Online-Kurs bereitgestellt.',
            ],
            [
                'name' => 'Sponsorship-linked Marketing',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Königstorfer',
                'type_of_class' => 'Online',
                'hours_per_week' => 4.0,
                'content' => 'An online course from vhb covering the theory and practice of sponsorship as a marketing communication tool. Topics include sponsorship objectives, evaluation methods, leveraging strategies, and the relationship between sponsors, rights holders, and audiences in sports, culture, and social contexts.',
                'goals' => 'Students will understand how sponsorship works as a marketing instrument and apply relevant frameworks to real-world cases.',
                'literature' => 'Course materials provided in the online platform.',
            ],
            [
                'name' => 'Verhandlungsführung, Konfliktmanagement und Mediation',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Scherer',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb zu den Grundlagen der Verhandlungsführung, Konfliktlösung und Mediation. Inhalte: Harvard-Konzept, Verhandlungsstrategien, Deeskalation und Mediationsverfahren in beruflichen und privaten Kontexten.',
                'goals' => 'Die Studierenden können Verhandlungen strukturiert führen, Konflikte analysieren und geeignete Lösungsansätze entwickeln.',
                'literature' => 'Fisher/Ury/Patton: Das Harvard-Konzept; Lehrmaterial im Online-Kurs.',
            ],
            [
                'name' => 'Vertragsgestaltung und Vertragsmanagement',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Scherer',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb. Grundlagen der Vertragsgestaltung im deutschen Recht: Vertragstypen, AGB, Haftung und Vertragsmanagement im unternehmerischen Umfeld. Praxisnahe Fallbeispiele illustrieren die rechtlichen Konzepte.',
                'goals' => 'Die Studierenden können einfache Verträge lesen, typische Klauseln einordnen und Grundprinzipien des Vertragsmanagements anwenden.',
                'literature' => 'Lehrmaterial wird im Online-Kurs bereitgestellt.',
            ],
            [
                'name' => 'English for Sustainable Technologies',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Field',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'An online vhb course building technical English vocabulary and communication skills in the context of sustainable technologies. Topics include renewable energy, circular economy, green engineering, and environmental policy, with a focus on reading, writing, and discussing technical texts.',
                'goals' => 'Students will communicate confidently about sustainability topics in English and produce clear written and oral contributions in professional settings.',
                'literature' => 'Course materials provided on the online platform.',
            ],
            [
                'name' => 'New Work - Digitale Transformation und Wertewandel',
                'credits' => 5.0,
                'language' => Language::German,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Winkler',
                'type_of_class' => 'Online',
                'hours_per_week' => 4.0,
                'content' => 'Onlinekurs der vhb zu Wandel der Arbeitswelt im Zuge der digitalen Transformation. Inhalte: New-Work-Konzepte, agile Methoden, Remote Work, Führung im digitalen Zeitalter, KI im Arbeitskontext und gesellschaftlicher Wertewandel.',
                'goals' => 'Die Studierenden analysieren aktuelle Veränderungen der Arbeitswelt und können New-Work-Prinzipien auf konkrete organisationale Kontexte anwenden.',
                'literature' => 'Laloux: Reinventing Organizations; Lehrmaterial im Online-Kurs.',
            ],
            [
                'name' => 'Betriebswirtschaftliche Grundlagen für Ingenieure',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Fieber',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb. Einführung in betriebswirtschaftliche Grundkonzepte für Ingenieure: Kostenrechnung, Investitionsrechnung, Marketing, Organisation und Unternehmensführung. Praxisnahe Fallbeispiele aus dem Ingenieurbereich.',
                'goals' => 'Die Studierenden verstehen betriebswirtschaftliche Zusammenhänge und können grundlegende BWL-Konzepte im technischen Berufsalltag anwenden.',
                'literature' => 'Lehrmaterial wird im Online-Kurs bereitgestellt.',
            ],
            [
                'name' => 'Management von Technologien und Innovationen',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Augsdörfer',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb zu strategischem Technologie- und Innovationsmanagement. Inhalte: Innovationsprozesse, F&E-Management, Technologielebenszyklen, Open Innovation und Geschäftsmodellinnovation in technologiegetriebenen Unternehmen.',
                'goals' => 'Die Studierenden kennen zentrale Instrumente des Innovations- und Technologiemanagements und können diese auf reale Unternehmenskontexte anwenden.',
                'literature' => 'Lehrmaterial wird im Online-Kurs bereitgestellt.',
            ],
            [
                'name' => 'Integriertes Qualitäts- und Umweltmanagement',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Ebinger',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb. Grundlagen integrierter Managementsysteme nach ISO 9001 und ISO 14001. Behandelt werden Qualitätssicherung, Umweltmanagementsysteme, Auditierung und die Verbindung beider Systeme in der Unternehmenspraxis.',
                'goals' => 'Die Studierenden kennen die Anforderungen der einschlägigen Normen und können integrierte Managementsysteme in Unternehmen beschreiben.',
                'literature' => 'ISO 9001:2015 und ISO 14001:2015; Lehrmaterial im Online-Kurs.',
            ],
            [
                'name' => 'Deep Learning for Beginners',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Maier',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'An online vhb course introducing deep learning for students without a machine learning background. Topics include neural network fundamentals, training procedures, convolutional networks for image recognition, and an overview of modern deep learning applications.',
                'goals' => 'Students will understand the basic concepts of deep learning and be able to discuss common architectures and their applications.',
                'literature' => 'Course materials provided on the online platform.',
            ],
            [
                'name' => 'Einführung in den 3D-Druck',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Babel',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Onlinekurs der vhb. Überblick über additive Fertigungsverfahren: FDM, SLA, SLS und weitere 3D-Drucktechnologien. Inhalte: Konstruktionsrichtlinien, Materialauswahl, Nachbearbeitung und Anwendungen in Industrie und Forschung.',
                'goals' => 'Die Studierenden kennen die gängigen 3D-Druckverfahren, können geeignete Technologien für verschiedene Anwendungen auswählen und einfache Modelle druckgerecht konstruieren.',
                'literature' => 'Lehrmaterial wird im Online-Kurs bereitgestellt.',
            ],
            [
                'name' => 'ERP Systems and Digital Transformation',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Dobhan',
                'type_of_class' => 'Online',
                'hours_per_week' => 4.0,
                'content' => 'An online vhb course covering enterprise resource planning (ERP) systems and their role in digital transformation. Topics include ERP architecture, implementation, SAP S/4HANA, business process integration, and the connection between ERP and digital strategy.',
                'goals' => 'Students will understand how ERP systems support business processes and digital transformation, and apply relevant concepts to practical case studies.',
                'literature' => 'Course materials provided on the online platform.',
            ],
            [
                'name' => 'Innovation & Entrepreneurship for Better Futures',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Spanjol',
                'type_of_class' => 'Online',
                'hours_per_week' => 4.0,
                'content' => 'An online vhb course on innovation and entrepreneurship with a sustainability focus. Students explore opportunity recognition, design thinking, business model development, and social entrepreneurship, applying these to challenges in health, environment, and society.',
                'goals' => 'Students will develop an entrepreneurial mindset and apply innovation frameworks to create solutions for societal and environmental challenges.',
                'literature' => 'Course materials provided on the online platform.',
            ],

            // 8. Online Degree Programs Only
            [
                'name' => 'Government and Law in Germany',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Förster',
                'type_of_class' => 'Online',
                'hours_per_week' => 4.0,
                'content' => 'A comprehensive online course for international students covering the German political system, federal structure, constitutional law, and the basics of civil and administrative law. Case studies and practical examples illustrate how the German legal and governmental system operates.',
                'goals' => 'Students will understand the structure of German government and law, enabling them to navigate legal and administrative contexts in Germany.',
                'literature' => 'Course materials provided on the online platform.',
            ],
            [
                'name' => 'Italienisch A1a (Online)',
                'credits' => 2.5,
                'language' => Language::German,
                'exam_type' => ExamType::Written,
                'lecturer' => 'Akacs',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'Online-Variante des Italienisch-Einführungskurses für Studierende in Online-Studiengängen. Grundlegende Grammatik, Wortschatz und Alltagskommunikation auf A1-Niveau.',
                'goals' => 'Die Studierenden können einfache Alltagssituationen auf Italienisch bewältigen und grundlegende Grammatikstrukturen anwenden.',
                'literature' => 'Allegro nuovo A1 Kurs- und Übungsbuch (Klett-Verlag, ISBN 978-3-12-525590-6).',
            ],
            [
                'name' => 'International Marketing',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Holtbrügge',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'This online course introduces the key concepts of marketing in an international context. Topics include market entry strategies, cross-cultural consumer behaviour, global branding, international pricing, and digital marketing in global markets.',
                'goals' => 'Students will apply international marketing frameworks to analyse global markets and develop marketing strategies for diverse cultural contexts.',
                'literature' => 'Course materials provided on the online platform.',
            ],
            [
                'name' => 'Strategic Human Resources Management',
                'credits' => 5.0,
                'language' => Language::English,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Ringlstetter',
                'type_of_class' => 'Online',
                'hours_per_week' => 4.0,
                'content' => 'A comprehensive online course on strategic HRM. Topics include talent acquisition and retention, performance management, HR strategy alignment with business objectives, diversity and inclusion, and HR in the context of digital transformation.',
                'goals' => 'Students will connect HR practices to organisational strategy and apply strategic HRM frameworks to contemporary workplace challenges.',
                'literature' => 'Course materials provided on the online platform.',
            ],
            [
                'name' => 'Leadership and Communication in Global Business',
                'credits' => 2.5,
                'language' => Language::English,
                'exam_type' => ExamType::Portfolio,
                'lecturer' => 'Winkler',
                'type_of_class' => 'Online',
                'hours_per_week' => 2.0,
                'content' => 'This online course addresses leadership and communication skills in global and cross-cultural business environments. Topics include leadership theories, intercultural communication, virtual team management, and professional writing and speaking in an international business context.',
                'goals' => 'Students will lead and communicate effectively in diverse international teams and apply global leadership principles to real-world scenarios.',
                'literature' => 'Course materials provided on the online platform.',
            ],
        ];
    }

    /**
     * Assign block session dates to an AWPF.
     *
     * AWPFs are elective courses with 2–3 specific offline sessions per semester.
     * Dates are spread across the semester, cycling through a set of predefined
     * Saturday block-day dates and standard morning/afternoon start times.
     */
    protected function createSchedules(Awpf $awpf, int $index, float $hoursPerWeek): void
    {
        // Typical THWS block session dates for a winter semester (adjust per real semester)
        $blockDates = [
            '2025-10-18', '2025-10-25', '2025-11-08', '2025-11-15',
            '2025-11-22', '2025-11-29', '2025-12-06', '2025-12-13',
            '2026-01-10', '2026-01-17', '2026-01-24', '2026-01-31',
        ];

        $startTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];
        $durationMinutes = $hoursPerWeek >= 4.0 ? 240 : 180;

        $date = $blockDates[$index % count($blockDates)];
        $time = $startTimes[$index % count($startTimes)];

        $awpf->schedules()->create([
            'scheduled_at' => Carbon::parse("{$date} {$time}"),
            'duration_minutes' => $durationMinutes,
        ]);

        // Courses with more hours get a second block session
        if ($hoursPerWeek >= 4.0) {
            $secondDate = $blockDates[($index + 4) % count($blockDates)];
            $secondTime = $startTimes[($index + 2) % count($startTimes)];

            $awpf->schedules()->create([
                'scheduled_at' => Carbon::parse("{$secondDate} {$secondTime}"),
                'duration_minutes' => $durationMinutes,
            ]);
        }
    }

    /**
     * Create or find a professor user from a name string.
     */
    protected function createOrFindProfessor(string $name): ?User
    {
        $nameParts = explode(' ', trim($name));
        $surname = array_pop($nameParts);
        $firstName = ! empty($nameParts) ? implode(' ', $nameParts) : $surname;

        $email = Str::slug($firstName.'.'.$surname, '.').'@thws.de';

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if (! $user->hasRole('professor')) {
                $user->assignRole('professor');
            }

            return $user;
        }

        $user = User::query()->create([
            'name' => $firstName,
            'surname' => $surname,
            'email' => $email,
            'password' => bcrypt('password'),
        ]);

        $professorRole = Role::query()->firstOrCreate(['name' => 'professor']);
        $user->assignRole($professorRole);

        $this->command->info("  → Created professor: {$firstName} {$surname} ({$email})");

        return $user;
    }
}
