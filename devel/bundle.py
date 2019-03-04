import glob, os
from subprocess import call, run

base_dir = os.path.dirname(os.path.realpath(__file__))
app = os.path.basename(base_dir)

fragments = []
for p in glob.glob('src/layouts/**/*.html', recursive=True):
    c = '--in-file "' + (app + '/' + p).replace('\\', '/') + '"'
    fragments.append(c)

fragments = ' '.join(str(e) for e in fragments)

modules = []
for p in glob.glob('src/modules/**/*.html', recursive=True):
    c = '--in-file "' + (app + '/' + p).replace('\\', '/') + '"'
    modules.append(c)

modules = ' '.join(str(e) for e in modules)

cmd='polymer-bundler '\
    '--out-dir "build" '\
    '--manifest-out "build/manifest.json" '\
    '--root "../" '\
    '--shell "devel/src/kct-app.html" ' + fragments + ' ' + modules

# print(cmd)
os.system(cmd)