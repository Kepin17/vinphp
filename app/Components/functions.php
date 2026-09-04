<?php

/**
 * Optional override point for @ComponentName(...) calls.
 *
 * You don't need to register a component here — @Badge(...) auto-resolves
 * to app/Views/{elements,fragments,templates}/badge.php by naming
 * convention (PascalCase -> kebab-case), see App\Core\View::auto(). Just
 * create the view file and use it.
 *
 * Define a function here only when a component needs real logic beyond
 * what its view file's @php block can express (talking to another class,
 * short-circuiting the render entirely, etc.) — it then takes priority
 * over the auto-resolved view:
 *
 *   namespace App\Components;
 *
 *   function MyComponent(array $data = []): void
 *   {
 *       // ... whatever the view file's @php block can't do ...
 *       \App\Core\View::component('fragments/my-component', $data);
 *   }
 */

namespace App\Components;
