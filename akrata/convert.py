from flask import Blueprint, redirect, url_for, request, current_app, send_file
import os
import xlsxwriter
import xml.etree.ElementTree as ET
import subprocess

convertbp = Blueprint('convertbp',__name__)

@convertbp.route('/upload', methods=('POST',))
def convert():
    if request.method == 'POST':
        start_num = request.form['start_num']
        last_num = request.form['last_num']
        excelName = request.form['outputName']
        file = request.files['file']
        print(file.filename)
        file.save(os.path.join(current_app.instance_path, file.filename))

        i = 1;
        file = os.path.join(current_app.instance_path, file.filename)
        excel = os.path.join(current_app.instance_path, excelName)
        phpScript = os.path.join(current_app.instance_path, "transforms.php")
        xlfile = xlsxwriter.Workbook(excel)
        worksheet = xlfile.add_worksheet()
        tree = ET.parse(file)
        root = tree.getroot()
        start = start_num
        stop = last_num
        for child in root:
            if(child.attrib.get("lat") != None and (int(child[2].text) <= int(stop) and int(child[2].text) >= int(start))):
                proc = subprocess.Popen("php "+phpScript+" "+str(child.attrib.get("lat"))+" "+ str(child.attrib.get("lon")),shell=True,stdout = subprocess.PIPE)
                script_response = proc.stdout.read().decode('utf-8')# must decode from bytes
                print(script_response)
                print("point "+ child[2].text)
                worksheet.write("A"+str(i),script_response)
                worksheet.write("B"+str(i),child[2].text)
                worksheet.write("C"+str(i),child[1].text)
                i = i+1
        print("closing")
        xlfile.close()
        return send_file(excel, as_attachment=True, mimetype='application/vnd.ms-excel', attachment_filename=excelName)
    return "conversion problem, method not post"
