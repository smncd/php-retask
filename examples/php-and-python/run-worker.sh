#!/bin/bash

if ! command -v "python" &> /dev/null; then
    echo "'python' is required for this program to run, make sure it's installed.";
    exit 1;
fi

python -m venv .venv;

source .venv/bin/activate;

python -m pip install -r requirements.txt;

echo 'Running worker...';;

python worker.py;
