#!/usr/bin/python

import sys
import argparse
import os
import csv

def pywalker(path, newpath):
            # if newpath version of dirs_ doesn't exist, make it
#            if not(os.path.isdir(os.path.join(root, "..", newpath, dirs_) )):
#                os.mkdir(os.path.join(root, "..", newpath, dirs_) )

    dataset_list = ["p38a", "VEGFR2", "TIE2", "CatS1", "CatS2", "JAK2SC2", "JAK2SC3", "ABL1"]

    contents = os.listdir( path )

    # This would print all the files and directories
    for content in contents:
        

        for dataset in dataset_list:
            file2 = dataset + "_LigandScoringProtocol_2_methods.csv"
            file3 = dataset + "_LigandScoringProtocol_3_methods.csv"
            file23 = dataset + "_LigandScoringProtocol_23_methods_complete.csv"

            if os.path.exists(os.path.join(newpath, content, file3)):
                # if 3 and 2 exist, then need to concat them but skip header of 2
                if os.path.exists(os.path.join(newpath, content, file2)):
                    fout=open(os.path.join(newpath, content, file23),"a")

                    # first file:
                    for line in open(os.path.join(newpath, content, file3)):
                        fout.write(line)

                    f = open(os.path.join(newpath, content, file2))
                    f.next() # skip the header

                    for line in f:
                        fout.write(line)
                        #f.close() # not really needed

                    fout.close()                            

                    # if 2 doesn't exist, then can just do a file copy to new name ...
                else:
                    instruct = 'cp ' + os.path.join(newpath, content, file3) + ' ' + os.path.join(newpath, content, file23)
                    os.system(instruct)

            # if 3 doesn't exist, then just try with 2
            else:
                if os.path.exists(os.path.join(newpath, content, file2)):
                    os.system('cp ' + os.path.join(newpath, content, file2) + ' ' + os.path.join(newpath, content, file23))

            file4 = dataset + "_FreeEnergyProtocol_4_methods.csv"
            file5 = dataset + "_FreeEnergyProtocol_5_methods.csv"
            file45 = dataset + "_FreeEnergyProtocol_45_methods_complete.csv"

            if os.path.exists(os.path.join(newpath, content, file5)):
                # if 5 and 4 exist, then need to concat them but skip header of 4
                if os.path.exists(os.path.join(newpath, content, file4)):
                    fout=open(os.path.join(newpath, content, file45),"a")

                    # first file:
                    for line in open(os.path.join(newpath, content, file5)):
                        fout.write(line)

                    f = open(os.path.join(newpath, content, file4))
                    f.next() # skip the header

                    for line in f:
                        fout.write(line)
                        #f.close() # not really needed

                    fout.close()                            
                # if 4 doesn't exist, then can just do a file copy to new name ...
                else:
                    instruct = 'cp ' + os.path.join(newpath, content, file5) + ' ' + os.path.join(newpath, content, file45)
                    os.system(instruct)

            # if 5 doesn't exist, then just try with 4
            else:
                if os.path.exists(os.path.join(newpath, content, file4)):
                    os.system('cp ' + os.path.join(newpath, content, file4) + ' ' + os.path.join(newpath, content, file45))

if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('-o', '--output')
    parser.add_argument('-q', '--quizzical')
    parser.add_argument('-v', dest='verbose', action='store_true')
    args = parser.parse_args()
    # ... do something with args.output ...
    old_csvpath = "csvs/"
    new_csvpath = "newcsvs/"
    #pywalker(old_csvpath + args.quizzical)
    pywalker("csvs", "newcsvs")

#    print os.getcwd()

    
    #if newcsvs/X directory doesn't exist, mkdir ...
#    if os.path.isdir(new_csvpath + args.quizzical):
#        print "Exists"
#    else:
#        print "Does not exist"
#        os.mkdir(new_csvpath + args.quizzical)
