from __future__ import division
import CameraCoverage.camera as cc
import math
import numpy as np
from array import array
import traceback

__debug_info = {}
#__debug = True
__debug = False

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
    c.location[0] = 0.0
    c.location[1] = 0.0
    num_of_P = P.shape[0]
    Dist_of_Ls = dist_of_points(L)
    H_range , T_range = np.meshgrid(H_range, T_range)
    shape = H_range.shape
    minV = 10000
    minH = H_range[0]
    minT = T_range[0]
    
    if __debug:
        value_table = np.zeros(shape)
    for x in range(shape[0]):
        for y in range(shape[1]):
            H = H_range[x, y]
            T = T_range[x, y]
            
            c.location[2] = H
            c.tilt = T / 180.0 * math.pi
            
            L_mapping_from_P = np.array([c.Map(tmp_P[0], tmp_P[1]) for tmp_P in P])
            Dist_of_L_mapping_P = dist_of_points(L_mapping_from_P)
            v = dist(Dist_of_Ls, Dist_of_L_mapping_P)
            if __debug:
                value_table[x,y] = v
            if v < minV:
                minV = v
                minH = H 
                minT = T
    if __debug:
        __debug_info['H'] = H_range
        __debug_info['T'] = T_range
        __debug_info['V'] = value_table
    c.location[2] = minH
    c.tilt = minT / 180.0 * math.pi
    L_mapping_from_P = np.array([c.Map(tmp_P[0], tmp_P[1]) for tmp_P in P])
    Dist_of_L_mapping_P = dist_of_points(L_mapping_from_P)
    Dist_L_O = np.array([dist(tmp,[0.0,0.0]) for tmp in L_mapping_from_P])

    #print(Dist_L_O)
    '''
    Matr_P = np.mat([[L_mapping_from_P[0,0], L_mapping_from_P[1,0], L_mapping_from_P[2,0]],
                     [L_mapping_from_P[0,1], L_mapping_from_P[1,1], L_mapping_from_P[2,1]], 
                     [1, 1, 1]])
    Matr_L = np.mat([[L[0,0], L[1,0], L[2,0]],
                     [L[0,1], L[1,1], L[2,1]], 
                     [1, 1, 1]])
    R = Matr_L * (Matr_P.I)
    c.location[0] = R[0,2]
    c.location[1] = R[1,2]
    c.R = R.tolist() 
    '''
    M_1 = np.mat([[2*(L[0][0]-L[2][0]),2*(L[0][1]-L[2][1])],
                  [2*(L[1][0]-L[2][0]),2*(L[1][1]-L[2][1])]])
    M_2 = np.mat([[L[0][0]**2-L[2][0]**2+L[0][1]**2-L[2][1]**2+Dist_L_O[0]**2-Dist_L_O[2]**2],
                  [L[1][0]**2-L[2][0]**2+L[1][1]**2-L[2][1]**2+Dist_L_O[1]**2-Dist_L_O[2]**2]])
    R = M_1.I * M_2
    c.location[0] = R[0,0]
    c.location[1] = R[1,0]

    sum_ = 0.0
    sum_sin = 0.0
    baseLocation = np.array([c.location[0], c.location[1]])
    c.cos = []
    c.sin = []
    for i in range(num_of_P):
        baseRay = L[i] - baseLocation
        d1 = dist(L_mapping_from_P[i], np.array([0.0, 0.0])) 
        d2 = dist(baseRay, np.array([0.0, 0.0]))
        d3 = dist(L_mapping_from_P[i],baseRay)
        cos1 = (d1**2 + d2**2 - d3**2)/(2*d1*d2) 
        sin1 = (L_mapping_from_P[i,1] * baseRay[0] - L_mapping_from_P[i,0] * baseRay[1]) / d1 / d2
        c.cos.append(cos1)
        c.sin.append(sin1)
        cos_v = math.acos(cos1)
        sum_ += cos_v
        sum_sin += sin1
    c.pan = sum_ / num_of_P
    if sum_sin < 0 :
        c.pan = 2*math.pi - c.pan

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
        if __debug: 
            jsontxt = u'{"ccd_w":3264, "ccd_h":2448, "f":2065.063, "H_from":3, "H_to":20, "T_from":20, "T_to":80 ,"maxLength":120,"P":[[3137.52,1134.439024390244],[726.24,1093.1031894934333],[1603.44,1782.033771106942]], "L":[[13237011.387617,3778715.4903162],[13237022.136574,3778676.6746378],[13237041.245832,3778695.7838949]]}'

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
            num_of_P = P.shape[0]
            ccd_size = np.array([c.ccd_w/2.0, c.ccd_h/2.0])
            P = P - np.tile(ccd_size, (num_of_P, 1))
            c.P = P.tolist()
            c.L = L.tolist()
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
            if __debug:
                import matplotlib.pyplot as plt
                import matplotlib.cm as cm
                print("location:%f,%f",(c.location[0],c.location[1]))
                print("minH:%f,minT:%f",(c.location[2],c.tilt*180.0/math.pi))
                print("H:%f-%f,T:%f-%f",(H_from,H_to,T_from,T_to))
                plt.figure()
                im = plt.imshow(__debug_info['V'],interpolation='bilinear', origin='lower',
                cmap=cm.gray, extent=(H_from,H_to,T_from,T_to))
                plt.colorbar(im, orientation='horizontal', shrink=0.8)
                plt.show()
            if (H_space < 0.01 and T_space < 0.05) or H_space <= 0 or T_space <= 0:
                break
            
            H_from = c.location[2] - H_space
            H_to = c.location[2] + H_space
            T_from = c.tilt*180.0/math.pi - T_space
            T_to = c.tilt*180.0/math.pi + T_space
            
        c.polygon = c.FOVstatic(maxLength).tolist()
        print(json.dumps(c.__dict__))
    except Exception,e:
        print e
        print traceback.format_exc()
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
    

