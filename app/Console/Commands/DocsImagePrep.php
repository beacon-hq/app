<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Imagick;
use ImagickPixel;

class DocsImagePrep extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:image-prep {matches?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare images for deploy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!file_exists(base_path('docs/screenshots'))) {
            mkdir(base_path('docs/screenshots'), 0755, true);
        }

        foreach (glob(\base_path('/tests/Browser/screenshots/*.png')) as $filename) {
            if (\str_contains($filename, 'failure-')) {
                continue;
            }

            if ($this->argument('matches') !== null && !\str_contains($filename, $this->argument('matches'))) {
                continue;
            }

            $this->components->task('Preparing ' . basename($filename), function () use ($filename) {
                $image = new Imagick($filename);
                $image->setImageDepth(8);
                $image->setType(Imagick::IMGTYPE_TRUECOLORMATTE);
                $image->setInterlaceScheme(Imagick::INTERLACE_NO);

                // Round corners
                $image->roundCornersImage(8, 8);

                // Add drop shadow
                $clone = clone $image;
                $clone->setImageBackgroundColor(new ImagickPixel('black'));
                $clone->shadowImage(25, 3, 5, 5);
                $clone->compositeImage($image, Imagick::COMPOSITE_OVER, 0, 0);
                $image = $clone;

                $image->borderImage(new ImagickPixel('transparent'), 30, 30);

                // Save to docs/screenshots
                $targetPath = base_path('docs/screenshots/' . basename($filename));
                $image->writeImage($targetPath);
                $image->clear();
            });
        }
    }
}
