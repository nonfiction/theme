{
  description = "nonfiction theme package development environment";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    flake-utils.url = "github:numtide/flake-utils";
    treefmt-nix.url = "github:numtide/treefmt-nix";
    treefmt-nix.inputs.nixpkgs.follows = "nixpkgs";
  };

  outputs = inputs:
    inputs.flake-utils.lib.eachDefaultSystem (
      system: let
        pkgs = inputs.nixpkgs.legacyPackages.${system};
        treefmtEval = inputs.treefmt-nix.lib.evalModule pkgs ./treefmt.nix;
      in {
        formatter = treefmtEval.config.build.wrapper;

        devShells.default = pkgs.mkShell {
          packages = with pkgs; [
            treefmtEval.config.build.wrapper
            php83
            php83Packages.composer
            php83Packages.php-cs-fixer
            prettier
            git
          ];

          shellHook = ''
            echo "nonfiction theme package dev shell"
            echo "====================================="
            echo "PHP:          $(php -v | head -1)"
            echo "Composer:     $(composer --version 2>/dev/null)"
            echo "PHP-CS-Fixer: $(php-cs-fixer --version 2>/dev/null | head -1)"
            echo "Prettier:     $(prettier --version 2>/dev/null)"
          '';
        };
      }
    );
}
