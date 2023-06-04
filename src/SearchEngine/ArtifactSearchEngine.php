<?php

declare(strict_types=1);

namespace App\SearchEngine;

use App\Controller\Api\ArtifactsListController;
use App\Exception\ServiceException;
use App\Model\Software\Software;
use App\Model\Book\Book;
use App\Model\Magazine\Magazine;
use App\Model\Peripheral\Peripheral;
use App\Model\Computer\Computer;
use App\Model\Response\GenericArtifactResponse;

use DI\Container;
use DI\ContainerBuilder;
use Exception;

class ArtifactSearchEngine {

    private Container $container;
    private array $categories;

    public function __construct(
        string $containerPath = "config/container.php"
    ) {
        $builder = new ContainerBuilder();
        $builder->addDefinitions($containerPath);
        $this->container = $builder->build();
        $this->categories = ArtifactsListController::$categories;
    }

    /**
     * Select specific object by id and category
     * @param string $ObjectID The id to select
     * @param string $category The category to search in
     * @return object The object fetched
     * @throws ServiceException If no object is found
     */
    public function selectSpecificByIdAndCategory(string $ObjectID, string $category): object {
        try {
            $artifactServicePath = "App\\Service\\$category\\$category" . "Service";

            $artifactService = $this->container->get($artifactServicePath);

            return $artifactService->selectById($ObjectID);
        } catch (Exception | ServiceException) {
        }
        throw new ServiceException("Artifact with id [$ObjectID] in category [$category] not found!");
    }


    /**
     * Select generic object by id
     * @param string $ObjectID     The ObjectID to select
     * @return GenericArtifactResponse            The Object selected
     * @throws ServiceException If not found
     */
    public function selectGenericById(string $ObjectID): GenericArtifactResponse {
        foreach ($this->categories as $categoryName) {

            $artifactServicePath = "App\\Service\\$categoryName\\$categoryName" . "Service";

            $artifactService = $this->container->get($artifactServicePath);

            try {
                $result = $artifactService->selectById($ObjectID);

                return $this->$categoryName($result);
            } catch (ServiceException) {
            }
        }
        throw new ServiceException("Artifact with id [$ObjectID] not found!");
    }

    /**
     * Select generics objects
     * @param ?string $category  The category to search in
     * @param ?string $query The eventual query
     * @return array            The result array
     */
    public function selectGenerics(?string $category = null, ?string $query = null): array {
        $result = [];

        foreach ($this->categories as $categoryName) {
            if ($category && $categoryName !== $category) {
                continue;
            }

            $artifactServicePath = "App\\Service\\$categoryName\\$categoryName" . "Service";

            $artifactService = $this->container->get($artifactServicePath);

            $artifactRepoName = strtolower($categoryName) . "Repository";

            $unmappedResult = null;
            if ($query) {
                $unmappedResult = $artifactService->selectByKey($query);
            } else {
                $unmappedResult = $artifactService->selectAll();
            }
            if (count($unmappedResult) > 0) {

                foreach ($unmappedResult as $item) {
                    $mappedObject = $artifactService->$artifactRepoName->returnMappedObject(json_decode(json_encode($item), true));

                    $result[] = $this->$categoryName($mappedObject);
                }
            }
        }

        //SORT BY OBJECT ID
        usort($result, function ($a, $b) {
            return strcmp($a->ObjectID, $b->ObjectID);
        });
        return $result;
    }

    /**
     * Map a book object to a generic object
     * @param Book $obj The book object
     * @return GenericArtifactResponse The object mapped
     */
    public function Book(Book $obj): GenericArtifactResponse {
        $authors = [];
        if ($obj->Authors) {
            foreach ($obj->Authors as $author) {
                $authors[] = $author->firstname[0] . " " . $author->lastname;
            }
        }

        return new GenericArtifactResponse(
            $obj->ObjectID,
            $obj->Title,
            [
                'Publisher' => $obj->Publisher->Name,
                'Year' => $obj->Year,
                'ISBN' => $obj->ISBN ?? "-",
                'Pages' => $obj->Pages ?? "-",
                'Authors' => count($authors) > 0 ? implode(", ", $authors) : "Unknown"
            ],
            "Book",
            $obj->Note,
            $obj->Url,
            $obj->Tag
        );
    }

    /**
     * Map a computer object to a generic object
     * @param Computer $obj The computer object
     * @return GenericArtifactResponse The object mapped
     */
    public function Computer(Computer $obj): GenericArtifactResponse {

        $description = [
            'Year' => $obj->Year,
            'Cpu' => $obj->Cpu->ModelName . ' ' . $obj->Cpu->Speed,
            'Ram' => $obj->Ram->ModelName . ' ' . $obj->Ram->Size
        ];

        if(isset($obj->HddSize)){
            $description["Hdd size"] = $obj->HddSize;
        }

        if(isset($obj->Os)){
            $description["Os"] = $obj->Os->Name;
        }

        return new GenericArtifactResponse(
            $obj->ObjectID,
            $obj->ModelName,
            $description,
            "Computer",
            $obj->Note,
            $obj->Url,
            $obj->Tag
        );
    }

    /**
     * Map a magazine object to a generic object
     * @param Magazine $obj The magazine object
     * @return GenericArtifactResponse The object mapped
     */
    public function Magazine(Magazine $obj): GenericArtifactResponse {
        return new GenericArtifactResponse(
            $obj->ObjectID,
            $obj->Title,
            [
                'Magazine number' => $obj->MagazineNumber,
                'Publisher' => $obj->Publisher->Name,
                'Year' => $obj->Year
            ],
            "Magazine",
            $obj->Note,
            $obj->Url,
            $obj->Tag
        );
    }

    /**
     * Map a peripheral object to a generic object
     * @param Peripheral $obj The peripheral object
     * @return GenericArtifactResponse The object mapped
     */
    public function Peripheral(Peripheral $obj): GenericArtifactResponse {
        return new GenericArtifactResponse(
            $obj->ObjectID,
            $obj->ModelName,
            [
                'Peripheral type' => $obj->PeripheralType->Name
            ],
            "Peripheral",
            $obj->Note,
            $obj->Url,
            $obj->Tag
        );
    }

    /**
     * Map a software object to a generic object
     * @param Software $obj The software object
     * @return GenericArtifactResponse The object mapped
     */
    public function Software(Software $obj): GenericArtifactResponse {
        return new GenericArtifactResponse(
            $obj->ObjectID,
            $obj->Title,
            [
                'Os' => $obj->Os->Name,
                'Software Type' => $obj->SoftwareType->Name,
                'Support Type' => $obj->SupportType->Name
            ],
            "Software",
            $obj->Note,
            $obj->Url,
            $obj->Tag
        );
    }
}
