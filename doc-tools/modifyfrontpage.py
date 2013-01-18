import argparse

# parse arguments

parser = argparse.ArgumentParser(description='Patch index.html with frontpagepatch.txt')

parser.add_argument('-i', required=True, action='store', dest='fin', 
                    help='path to index.html')

parser.add_argument('-p', required=True, action='store', dest='fpn', 
                    help='path to frontpagepatch.txt')

args = parser.parse_args()

with open(args.fpn, 'r') as fp:
    p = fp.read()

with open(args.fin, 'r') as fi:
    while True:
        li1 = fi.readline()
        if li1 == '':
            break
        if li1 == "</div>\n":
            li2 = fi.readline()
            if li2 == "<div class=\"span5\">\n":
                li1 = p + li1
            li1 = li1 + li2
        print li1,
