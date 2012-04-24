Todo
=====


- Fix CLIFramework bug.

- implement precompile command.

- When fetching new asset, we should copy the files into --output directory (which is public directory to www)
    - We should save manifest paths and the paths of copied files.
    - When copying files into --output directory, we should check the checksum for each file, should not overwrite them.
- So that we can support debug flag (separating files to include)

- Support css rewrite filter.
    - Since we are copying css files into --output directory, we should also rewrite the 
        image paths with related css image url.
        
        for example, in public/assets/jquery/app.css
            
            background: url(images/bg.png);

        should be rewrited into:

            background: url(/assets/jquery/images/bg.png);

