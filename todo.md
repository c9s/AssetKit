Todo
=====
- implement precompile command.

- So that we can support debug flag (separating files to include)

- Add css import filter.
- Add css rewrite filter.

    - Since we are copying css files into --output directory, we should also rewrite the 
        image paths with related css image url.

        image copy
        
        for example, in public/assets/jquery/app.css
            
            background: url(images/bg.png);

        should be rewrited into:

            background: url(/assets/jquery/images/bg.png);

    - Front testing code (load asset from assetLoader) from other path.

- Twig extension for including assets

Done
====
x Fix CLIFramework bug.
