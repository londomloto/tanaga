var config = {
  import: {
    options: {
      indent: true
    },
    build: {
      files: {
        "_build/relative-time.html": "src/relative-time.html",
        "_build/relative-time.js": "src/relative-time.js"
      }
    }
  },

  sass: {
    options: {
      sourcemap: 'none',
      outputStyle: 'compressed'
    },
    build: {
      files: {
        "_build/relative-time.css": "src/relative-time.scss"
      }
    }
  },

  /*
    Add css prefixes for compatibility (mainly for Firefox ESL)
   */
  postcss: {
    options: {
      map: false,
      processors: [
        require('autoprefixer')({
          browsers: ['firefox 45', 'last 2 versions']
        })
      ]
    },
    build: {
      files: {
        "_build/relative-time.css": "_build/relative-time.css"
      }
    }
  },

  htmlmin: {
    options: {
      removeComments: true,
      collapseWhitespace: false,
      ignoreCustomComments: [ /^\n`/ ]
    },
    build: {
      files: {
        "_build/relative-time.html": "_build/relative-time.html"
      }
    }
  },

  inline: {
    html: {
      files: {
        "relative-time.html": "_build/relative-time.html"
      }
    }
  },

  clean: {
    build: {
      src: "_build"
    }
  },

  prettify: {
    options: {
      indent: 2,
      indent_char: ' ',
      wrap_line_length: 78,
      brace_style: 'end-expand'
    },
    build: {
      files: {
        "relative-time.html": "relative-time.html"
      }
    }
  }
}

module.exports = grunt => {

  grunt.initConfig(config);

  grunt.loadNpmTasks('grunt-import');
  grunt.loadNpmTasks('grunt-postcss');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-inline');
  grunt.loadNpmTasks('grunt-prettify');
  grunt.loadNpmTasks('grunt-contrib-htmlmin');

  grunt.registerTask('debug', ['import', 'sass', 'postcss', 'htmlmin', 'inline', 'prettify'])
  grunt.registerTask('default', ['debug', 'clean'])
};
