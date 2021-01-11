#!/usr/bin/env python3
import os
import json
import sys
def get_list_of_dirs(path):
    output_dictonary = {}
    list_of_dirs = [os.path.join(path, item) for item in os.listdir(path) if os.path.isdir(os.path.join(path, item))]
    output_dictonary['text'] = path.rsplit('/',1)[1] 
    output_dictonary['id'] = path 
    #path.rsplit('/',1)[1] 

    output_dictonary["type"] = "directory"
    output_dictonary['children'] = []
    list_of_files = [os.path.join(path, item) for item in os.listdir(path) if os.path.isfile(os.path.join(path, item)) and not item.startswith('.')]
    for file in list_of_files:
        temp = {}
        temp['text'] = file.rsplit('/',1)[1]  
        temp['id'] = file
        temp["type"] = "file"
        output_dictonary['children'].append(temp)
    for dir in list_of_dirs:
        output_dictonary['children'].append(get_list_of_dirs(dir))
    return output_dictonary
    
print(json.dumps(get_list_of_dirs("/opt/varlik")))