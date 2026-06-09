{pkgs, ...}: {
  projectRootFile = "flake.nix";

  programs.alejandra.enable = true;
  settings.formatter.alejandra.includes = [
    "*.nix"
    "**/*.nix"
  ];

  programs.php-cs-fixer = {
    enable = true;
    configFile = "./.php-cs-fixer.dist.php";
  };
  settings.formatter.php-cs-fixer.includes = pkgs.lib.mkForce [
    "src/**/*.php"
  ];

  settings.formatter.prettier = {
    command = pkgs.lib.getExe pkgs.prettier;
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
