<?php

declare(strict_types=1);

use App\Values\TypescriptTransformer;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon as LaravelCarbon;
use Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

return [
    /*
     * The paths where typescript-transformer will look for PHP classes
     * to transform, this will be the `app` path by default.
     */

    'auto_discover_types' => [
        app_path()
    ],

    /*
     * Collectors will search for classes in the `auto_discover_types` paths and choose the correct
     * transformer to transform them. By default, we include a DefaultCollector which will search
     * for @typescript annotated and #[TypeScript] attributed classes to transform.
     */

    'collectors' => [
        DefaultCollector::class,
    ],

    /*
     * Transformers take PHP classes(e.g., enums) as an input and will output
     * a TypeScript representation of the PHP class.
     */

    'transformers' => [
        SpatieStateTransformer::class,
        EnumTransformer::class,
        TypescriptTransformer::class,
    ],

    /*
     * In your classes, you sometimes have types that should always be replaced
     * by the same TypeScript representations. For example, you can replace a
     * Datetime always with a string. You define these replacements here.
     */

    'default_type_replacements' => [
        \DateTime::class => 'string',
        \DateTimeImmutable::class => 'string',
        CarbonInterface::class => 'string',
        CarbonImmutable::class => 'string',
        Carbon::class => 'string',
        LaravelCarbon::class => 'string',
    ],

    /*
     * The package will write the generated TypeScript to this file.
     */

    'output_file' => resource_path('/js/Application.ts'),

    /*
     * When the package is writing types to the output file, a writer is used to
     * determine the format. By default, this is the `TypeDefinitionWriter`.
     * But you can also use the `ModuleWriter` or implement your own.
     */

    'writer' => ModuleWriter::class,

    /*
     * The generated TypeScript file can be formatted. We ship a Prettier formatter
     * out of the box: `PrettierFormatter` but you can also implement your own one.
     * The generated TypeScript will not be formatted when no formatter was set.
     */

    'formatter' => PrettierFormatter::class,

    /*
     * Enums can be transformed into types or native TypeScript enums, by default
     * the package will transform them to types.
     */

    'transform_to_native_enums' => true,
];
