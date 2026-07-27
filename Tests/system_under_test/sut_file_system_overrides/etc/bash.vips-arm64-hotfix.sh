if [ "$(uname -m)" = "aarch64" ]; then
  echo "Using LD_Preload workaround for gomp issue"

  # WORKAROUND for Apple M1 Chips. Without this line, we get the error message:
  #
  #             Warning: PHP Startup: Unable to load dynamic library 'vips.so' (tried:
  #             /usr/local/lib/php/extensions/no-debug-non-zts-20200930/vips.so
  #             (/usr/lib/aarch64-linux-gnu/libgomp.so.1: cannot allocate memory in
  #             static TLS block), /usr/local/lib/php/extensions/no-debug-non-zts-20200930/vips.so.so
  #             (/usr/local/lib/php/extensions/no-debug-non-zts-20200930/vips.so.so: cannot open
  #             shared object file: No such file or directory)) in Unknown on line 0
  #
  # This error seems to be related to some OpenCV bug or issue described at
  # https://github.com/opencv/opencv/issues/14884#issuecomment-706725583
  # And the workaround is to ensure that libgomp is loaded first.
  export LD_PRELOAD=/usr/lib/aarch64-linux-gnu/libgomp.so.1
fi
