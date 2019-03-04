@ECHO OFF
polymer-bundler^
 --out-dir "build"^
 --manifest-out "build/manifest.json"^
 --root "../"^
 --shell "devel/src/kct-app.html"^
 --in-file "devel/src/layouts/main-layout.html"^
 --in-file "devel/src/layouts/auth-layout.html"^
 --in-file "devel/src/layouts/page-layout.html"^
 --in-file "devel/src/layouts/error-layout.html"
 rem --in-file "devel/src/modules/auth/login-page.html"^
 rem --in-file "devel/src/modules/auth/forgot-page.html"