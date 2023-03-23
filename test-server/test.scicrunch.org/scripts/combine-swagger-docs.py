import json
import re
import urllib2

f = open("../swagger-docs/swagger-sc.json")
swagger1 = json.loads(f.read())
swagger2json = urllib2.urlopen("http://matrix.neuinfo.org:9000/scigraph/swagger.json").read()
swagger2json1 = re.sub(r'#/definitions/(\w+)', r'#/definitions/SG\1', swagger2json)
swagger2 = json.loads(swagger2json1)
for path in swagger2["paths"]:
    pathdict = swagger2["paths"][path]
    new_path = "/scigraph" + path
    for method in pathdict:
        for i in range(len(pathdict[method]["tags"])):
            pathdict[method]["tags"][i] = "SciGraph " + pathdict[method]["tags"][i]
    swagger1["paths"][new_path] = pathdict
for definition in swagger2["definitions"]:
    swagger1["definitions"]["SG" + definition] = swagger2["definitions"][definition]
swagger1["tags"] = swagger2["tags"]
f_w = open("../swagger-docs/swagger.json", "w")
f_w.write(json.dumps(swagger1))
f_w.close()
