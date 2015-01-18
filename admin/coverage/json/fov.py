'''
Created on 2014-11-15

@author: zhanganam
'''
from __future__ import division 
from math import pi
import CameraCoverage.camera as cc

if __name__ == '__main__':
    import sys
    import getopt
    import json
    try:
        opts ,args = getopt.getopt(sys.argv[1:], 'j', ['json='])
        for o,a in opts:
            if o in ('-j','--j','--json','-json'):
                jsontxt = a
        jsonarr = json.loads(jsontxt)
        c = cc.Camera(0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0)
        for keyword in ("ccd_w","ccd_h","f","pan","tilt","location"):
            if keyword in jsonarr.keys():
                c.__dict__[keyword] = jsonarr[keyword]
            else:
                raise Exception
        c.pan = c.pan * pi / 180.0
        c.tilt = c.tilt * pi / 180.0
        if "maxLength" in jsonarr.keys():
            maxLength = jsonarr["maxLength"]
        else:
            maxLength = -1
        c.polygon = c.FOVstatic(maxLength).tolist()
        print(json.dumps(c.__dict__))
    except:
        print(json.dumps({"error":1}))
    else:
        pass    