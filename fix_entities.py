import os
import re

entities = ['Type.php', 'SousType.php', 'Illumination.php', 'Orientation.php', 'Substrat.php', 'Superficie.php', 'Specification.php', 'Taille.php']
base_dir = '/Volumes/KONATE/PROJET KONATE/DOUDOU/global/src/Entity/'

code_prop = """
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $code = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }
"""

for entity in entities:
    path = os.path.join(base_dir, entity)
    if not os.path.exists(path):
        print(f"Skipping {path}, not found.")
        continue
    
    with open(path, 'r') as f:
        content = f.read()
    
    if 'private ?string $code = null;' in content:
        print(f"Already added in {entity}")
        continue
        
    # We want to insert it before the closing brace of the class
    # Actually, a better place is just before the constructor or any existing method
    # Let's find "public function __construct()"
    if 'public function __construct()' in content:
        content = content.replace('public function __construct()', code_prop + '\n    public function __construct()')
    else:
        # If no construct, find the first method
        if 'public function getId()' in content:
            content = content.replace('public function getId()', code_prop + '\n    public function getId()')
        else:
            # Insert before last brace
            content = re.sub(r'}\s*$', code_prop + '\n}\n', content)
            
    with open(path, 'w') as f:
        f.write(content)
    print(f"Updated {entity}")

