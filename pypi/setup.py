from setuptools import setup, find_packages

with open("README.md", "r", encoding="utf-8") as fh:
    long_description = fh.read()

setup(
    name="eczaneapi",
    version="1.0.0",
    author="BetaSoft Yazılım",
    author_email="betasoftyazilim@gmail.com",
    description="Türkiye 81 İl ve 973 İlçesi İçin Resmi ve Güncel Nöbetçi Eczane API Python İstemcisi",
    long_description=long_description,
    long_description_content_type="text/markdown",
    url="https://github.com/betasoftyazilim/eczaneapi",
    packages=find_packages(),
    classifiers=[
        "Programming Language :: Python :: 3",
        "License :: OSI Approved :: MIT License",
        "Operating System :: OS Independent",
    ],
    python_requires=">=3.8",
)
