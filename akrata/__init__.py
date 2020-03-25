from flask import Flask
import os

def create_app():
    app = Flask(__name__, instance_relative_config=False)
    app.config.from_object('config.Config')

    try:
        os.makedirs(app.instance_path)
    except OSError:
        pass

    from . import convert
    from . import index
    app.register_blueprint(convert.convertbp)
    app.register_blueprint(index.indexbp)

    return app
