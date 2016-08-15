if [ ! -e './site-packages' ]; then
    pip install -t ./site-packages -r requirements.txt
fi
python ./main.py