Asset Manifest
=================


Setting up asset resource from GitHub:

```yml
---
resource:
  github: jquery/jquery
  commands:
    - git submodule init
    - git submodule update
    - make
```

