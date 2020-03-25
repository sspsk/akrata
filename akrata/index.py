from flask import Blueprint, render_template
import os

indexbp = Blueprint('indexbp', __name__)

@indexbp.route('/', methods=('GET', 'POST'))
def index():
    return render_template('index.html')
