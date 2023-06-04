<?php

declare(strict_types=1);

namespace App\Model\Software;

use App\Model\Computer\Os;
use App\Model\GenericObject;

class Software extends GenericObject {

    public string $Title;
    public Os $Os;
    public SoftwareType $SoftwareType;
    public SupportType $SupportType;

    public function __construct(
        string $ObjectID,
        string $Note = null,
        string $Url = null,
        string $Tag = null,
        string $Title,
        Os $Os,
        SoftwareType $SoftwareType,
        SupportType $SupportType
    ) {
        parent::__construct($ObjectID, $Note, $Url, $Tag);
        $this->Title = $Title;
        $this->Os = $Os;
        $this->SoftwareType = $SoftwareType;
        $this->SupportType = $SupportType;
    }
}
