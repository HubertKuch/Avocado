<?php

namespace Avocado\AvocadoApplication\Files;

use Avocado\AvocadoApplication\Files\Exceptions\CannotMoveFileException;
use Avocado\AvocadoApplication\Files\Exceptions\FileExistsException;
use Avocado\HTTP\ContentType;
use PHPUnit\Exception;
use Throwable;

class MultipartFile {

    public function __construct(private readonly string $name, private readonly float $sizeInBytes, private readonly string $tmpPath, private readonly int $errorCode, private readonly ContentType $mime) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getMime(): ContentType {
        return $this->mime;
    }


    public function getSizeInBytes(): float {
        return $this->sizeInBytes;
    }

    public function getErrorCode(): int {
        return $this->errorCode;
    }

    /**
     * @description Moves file to directory
     * @param string $path Full path with file name
     * @throws FileExistsException
     * @throws CannotMoveFileException
     */
    public function moveTo(string $path): void {
        try {
            if(file_exists($path)) {
                throw new FileExistsException("File in `$path` path exists.");
            }

            $file = fopen($path, "w");
            $content = file_get_contents($this->tmpPath);

            fwrite($file, $content);
        } catch (Throwable $e) {
            var_dump($e);
            throw new CannotMoveFileException("Cannot move file to `$path`", 1, $e);
        }
    }

    public function getTmpPath(): string {
        return $this->tmpPath;
    }
}