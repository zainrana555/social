<?php

declare(strict_types=1);

namespace App\Dto\Response\Transformer;

use App\Dto\Exception\UnexpectedTypeException;
use App\Dto\Response\ImageResponseDto;
use App\Entity\Image;

class ImageResponseDtoTransformer extends AbstractResponseDtoTransformer
{
    /**
     * @param Image $image
     *
     * @return ImageResponseDto
     */
    public function transformFromObject($image): ImageResponseDto
    {
        if (!$image instanceof Image) {
            throw new UnexpectedTypeException('Expected type of Image but got ' . \get_class($image));
        }

        $dto = new ImageResponseDto();
        $dto->id = $image->getId();
        $dto->path = $image->getPath();

        return $dto;
    }
}
