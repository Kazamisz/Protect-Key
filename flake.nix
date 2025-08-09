{
  description = "Ambiente PHP + Apache + Composer para Railway (sem conflitos)";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-24.05"; # fixa versão estável
  };

  outputs = { self, nixpkgs }:
    let
      system = "x86_64-linux";
      pkgs = import nixpkgs { inherit system; };
      
      apacheNoMime = pkgs.apacheHttpd.overrideAttrs (old: {
        postInstall = (old.postInstall or "") + ''
          rm $out/conf/mime.types || true
        '';
      });
    in {
      devShells.${system}.default = pkgs.mkShell {
        packages = with pkgs; [
          php
          phpPackages.composer
          apacheNoMime
        ];

        shellHook = ''
          echo "Ambiente PHP + Apache + Composer pronto."
          echo "Use: vendor/bin/heroku-php-apache2 public/"
        '';
      };
    };
}
