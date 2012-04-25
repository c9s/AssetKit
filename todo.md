Todo
=====
- implement precompile command.


- When installing files, we should only copy them into the public asset root,
	- Then provide a method to get file paths based on public asset root.

	- When running asset writer from compile command:
		- make sure the apc is working in CLI mode.
		- run filters and compressors

	- When running asset writer from web front-end:
		development:
			run filters
		produnction:
			run filters, then compressors

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
