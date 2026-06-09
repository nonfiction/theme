{
  projectRootFile = "flake.nix";

  programs.alejandra = {
    enable = true;
    includes = [
      "*.nix"
      "**/*.nix"
    ];
  };

  programs.php-cs-fixer = {
    enable = true;
    configFile = "./.php-cs-fixer.dist.php";
    includes = [
      "src/**/*.php"
    ];
  };

  programs.prettier = {
    enable = true;
    includes = [
      "*.json"
      "**/*.json"
      "*.md"
      "**/*.md"
    ];
  };

  settings.global.excludes = [
    "vendor/**"
  ];
}
