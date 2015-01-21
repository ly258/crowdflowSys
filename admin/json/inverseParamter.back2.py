from __future__ import division
import CameraCoverage.camera as cc
import math
import numpy as np
from array import array

def dist(X, Y):
    return sum((X - Y) ** 2) ** 0.5

def dist_of_points(Points):
    num_of_P = Points.shape[0]
    Dist_of_Ps = np.zeros(num_of_P * (num_of_P-1) /2)
    index = 0
    for i in range(num_of_P-1):
        for j in range(i+1,num_of_P):
            Dist_of_Ps[index] = dist(Points[i], Points[j])
            index += 1
    return Dist_of_Ps

def inverse(c, H_range, T_range, P, L):
    '''
    c - camera with CCD_W, CCD_H, f
    H_range - range of Hight
    T_range - range of tilt
    P - points in image , shape 2*N 
    L - points in map ,  shape 2*N
    '''
    num_of_P = P.shape[0]
    ccd_size = np.array([c.ccd_w/2.0, c.ccd_h/2.0])
    P = P - np.tile(ccd_size, (num_of_P, 1))
    Dist_of_Ls = dist_of_points(L)
    H_range , T_range = np.meshgrid(H_range, T_range)
    shape = H_range.shape
    minV = 10000
    minH = H_range[0]
    minT = T_range[0]
    
    for x in range(shape[0]):
        for y in range(shape[1]):
            H = H_range[x, y]
            T = T_range[x, y]
            c.location[2] = H
            c.tilt = T / 180.0 * math.pi
            
            L_mapping_from_P = np.array([c.Map(tmp_P[0], tmp_P[1]) for tmp_P in P])
            Dist_of_L_mapping_P = dist_of_points(L_mapping_from_P)
            v = dist(Dist_of_Ls, Dist_of_L_mapping_P)
            if v < minV:
                minV = v
                minH = H 
                minT = T
    c.location[2] = minH
    c.tilt = minT / 180.0 * math.pi
    L_mapping_from_P = np.array([c.Map(tmp_P[0], tmp_P[1]) for tmp_P in P])
    Dist_of_L_mapping_P = dist_of_points(L_mapping_from_P)
    Matr_P = np.mat([[L_mapping_from_P[0,0], L_mapping_from_P[1,0], L_mapping_from_P[2,0]],
                     [L_mapping_from_P[0,1], L_mapping_from_P[1,1], L_mapping_from_P[2,1]], 
                     [1, 1, 1]])
    Matr_L = np.mat([[L[0,0], L[1,0], L[2,0]],
                     [L[0,1], L[1,1], L[2,1]], 
                     [1, 1, 1]])
    #R = Matr_L * (Matr_P.I)
    R = Matr_P * (Matr_L.I)
    c.location[0] = R[0,2]
    c.location[1] = R[1,2]
    
    sum_ = 0.0
    baseLocation = np.array([R[0, 2], R[1, 2]])
    for i in range(num_of_P):
        baseRay = L[i] - baseLocation
        sin1 = (L_mapping_from_P[i,1] * baseRay[0] + L_mapping_from_P[i,0] * baseRay[1]) / dist(L_mapping_from_P[i], np.array([0.0, 0.0])) / dist(baseRay, np.array([0.0, 0.0]))
        sum_ += math.acos(sin1)
    c.pan = sum_ / num_of_P
    return c


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
        for keyword in ("ccd_w","ccd_h","f"):
            if keyword in jsonarr.keys():
                if jsonarr[keyword] is array:
                    c.__dict__[keyword] = np.array(jsonarr[keyword])
                else:
                    c.__dict__[keyword] = jsonarr[keyword]
            else:
                raise Exception
        
        if set(["H_from","H_to","T_from","T_to","P","L"]) < set(jsonarr.keys()):
            H_from = jsonarr["H_from"]
            H_to = jsonarr["H_to"]
            T_from = jsonarr["T_from"]
            T_to = jsonarr["T_to"]
            P = np.array(jsonarr["P"])
            L = np.array(jsonarr["L"])
        else:
            raise Exception
          
        if "maxLength" in jsonarr.keys():
            maxLength = jsonarr["maxLength"]
        else:
            maxLength = 120.0
        
        while True:
            H_space = (H_to-H_from)/20
            T_space = (T_to-T_from)/20
            H_range = np.arange(H_from,H_to,H_space)
            T_range = np.arange(T_from,T_to,T_space)
            #print(H_space,T_space)
            c = inverse(c, H_range, T_range, P,L)
            
            if H_space > 0.01 or T_space > 0.05 or H_space < 0 or T_space < 0:
                break
            
            H_from = c.location[2] - H_space
            H_to = c.location[2] + H_space
            T_from = c.tilt - T_space
            T_to = c.tilt + T_space
            
        c.polygon = c.FOVstatic(maxLength).tolist()
        print(json.dumps(c.__dict__))
    except:
        print(json.dumps({"error":1}))
    else:
        pass
        '''
        ccd_w = 3264.0
        ccd_h = 2448.0
        f = 2056.063
        P = np.array([[2591.0, 1247.0], [748.0, 1094.0], [1741.0, 1413.0],[378.0,1277.0]])
        L = np.array([[680214.251, 3555011.106], 
                      [ 680214.391, 3554986.243], 
                      [680222.162, 3555001.545],
                      [680222.490,3554978.440]])
        H_range = np.arange(13, 16, 0.1)
        T_range =  np.arange(20, 60, 0.5)
        
        c = cc.Camera(0.0, 0.0, 0.0, 0.0, 0.0, ccd_w, ccd_h, f)
        c = inverse(c, H_range, T_range, P, L)
        '''
    

