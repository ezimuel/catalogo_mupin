<?php

declare(strict_types=1);

namespace App\Test\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use App\SearchEngine\ArtifactSearchEngine;
use App\Exception\RepositoryException;
use App\Exception\ServiceException;
use App\Model\Book\Publisher;
use App\Model\Computer\Computer;
use App\Model\Computer\Cpu;
use App\Model\Computer\Os;
use App\Model\Computer\Ram;
use App\Model\Magazine\Magazine;
use App\Repository\Book\AuthorRepository;
use App\Repository\Book\BookAuthorRepository;
use App\Repository\Book\BookRepository;
use App\Repository\Book\PublisherRepository;
use App\Repository\Computer\ComputerRepository;
use App\Repository\Computer\CpuRepository;
use App\Repository\Computer\OsRepository;
use App\Repository\Computer\RamRepository;
use App\Repository\Magazine\MagazineRepository;
use App\Repository\Peripheral\PeripheralRepository;
use App\Repository\Peripheral\PeripheralTypeRepository;
use App\Repository\Software\SoftwareRepository;
use App\Repository\Software\SoftwareTypeRepository;
use App\Repository\Software\SupportTypeRepository;
use App\Util\DIC;

final class ArtifactSearchEngineTest extends TestCase
{
    public static ArtifactSearchEngine $artifactSearchEngine;
    public static SoftwareRepository $softwareRepository;
    public static ComputerRepository $computerRepository;
    public static BookRepository $bookRepository;
    public static MagazineRepository $magazineRepository;
    public static OsRepository $osRepository;
    public static CpuRepository $cpuRepository;
    public static RamRepository $ramRepository;
    public static PeripheralRepository $peripheralRepository;

    public static ?PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = RepositoryTestUtil::getTestPdo();

        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);
        self::$pdo = RepositoryTestUtil::createTestDB(self::$pdo);

        self::$softwareRepository = new SoftwareRepository(
            self::$pdo,
            new SoftwareTypeRepository(self::$pdo),
            new SupportTypeRepository(self::$pdo),
            new OsRepository(self::$pdo)
        );

        self::$cpuRepository = new CpuRepository(self::$pdo);
        self::$ramRepository = new RamRepository(self::$pdo);
        self::$osRepository = new OsRepository(self::$pdo);

        self::$computerRepository = new ComputerRepository(
            self::$pdo,
            self::$cpuRepository,
            self::$ramRepository,
            self::$osRepository
        );

        self::$bookRepository = new BookRepository(
            self::$pdo,
            new PublisherRepository(self::$pdo),
            new AuthorRepository(self::$pdo),
            new BookAuthorRepository(self::$pdo)
        );

        $publisherRepository = new PublisherRepository(self::$pdo);
        self::$magazineRepository = new MagazineRepository(
            self::$pdo,
            $publisherRepository
        );

        self::$peripheralRepository = new PeripheralRepository(
            self::$pdo,
            new PeripheralTypeRepository(self::$pdo)
        );

        self::$artifactSearchEngine = new ArtifactSearchEngine(
            "config/test_container.php"
        );

        $cpu = new Cpu("I7", "4GHZ", 1);
        $ram = new Ram("Ram 1", "64GB", 1);
        $os = new Os("Windows 10", 1);
        $publisher = new Publisher("Einaudi", 1);

        self::$cpuRepository->insert($cpu);
        self::$ramRepository->insert($ram);
        self::$osRepository->insert($os);

        self::$computerRepository->insert(new Computer(
            "OBJ1",
            null,
            null,
            null,
            "Computer1",
            2018,
            "1TB",
            $cpu,
            $ram,
            $os
        ));

        $publisherRepository->insert($publisher);

        self::$magazineRepository->insert(new Magazine(
            "OBJ2",
            null,
            null,
            null,
            "Compass",
            2017,
            23,
            $publisher
        ));
    }

    //SELECT TESTS
    public function testGoodSelectGenericById(): void
    {
        $obj = self::$artifactSearchEngine->selectGenericById("OBJ1");
        $this->assertEquals(
            [
                "Year" => 2018,
                "Hdd size" => "1TB",
                "Cpu" => "I7 4GHZ",
                "Ram" => "Ram 1 64GB",
                "Os" => "Windows 10"
            ],
            $obj->Descriptors
        );
        $this->assertEquals("Computer1", $obj->Title);
    }


    public function testBadSelectGenericById(): void
    {
        $this->expectException(ServiceException::class);
        self::$artifactSearchEngine->selectGenericById("wrong");
    }

    public function testGoodSelectSpecificByIdAndCategory(): void
    {
        $obj = self::$artifactSearchEngine->selectSpecificByIdAndCategory("OBJ1","Computer");
        $this->assertEquals(2018,$obj->Year);
        $this->assertEquals("Computer1", $obj->ModelName);
    }

    public function testBadSelectSpecificByIdAndCategory(): void
    {
        $this->expectException(ServiceException::class);
        self::$artifactSearchEngine->selectSpecificByIdAndCategory("OBJ1","wrong-category");
    }

    public function testBadSelectSpecificByIdAndCategory2(): void
    {
        $this->expectException(ServiceException::class);
        self::$artifactSearchEngine->selectSpecificByIdAndCategory("wrong","Computer");
    }

    public function testGoodSelectAll(): void
    {
        $this->assertEquals(count(self::$artifactSearchEngine->selectGenerics()), 2);
    }

    public function testGoodSelectByQuery(): void
    {
        $result = self::$artifactSearchEngine->selectGenerics(null, "cOmP");
        $this->assertEquals(2, count($result));
    }

    public function testGoodSelectByQueryWithCategory(): void
    {
        $result = self::$artifactSearchEngine->selectGenerics("Computer", "cOmP");
        $this->assertEquals(1, count($result));
    }

    public function testBadSelectByQuery(): void
    {
        $result = self::$artifactSearchEngine->selectGenerics("WRONG", null);
        $this->assertEquals(0, count($result));
    }

    public function testBadSelectByQueryWithWrongCategory(): void
    {
        $result = self::$artifactSearchEngine->selectGenerics("magazine", "comp");
        $this->assertEquals(0, count($result));
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);
        self::$pdo = null;
    }
}
