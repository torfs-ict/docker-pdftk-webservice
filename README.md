# docker-pdftk-webservice

Docker image to run PDFtk as a webservice.

If you prefer a pre-build version it is available from [hub.docker.com](https://hub.docker.com/r/torfsict/docker-pdftk-webservice)
just do a regular pull

```bash
$ docker pull torfsict/docker-pdftk-webservice
```

## Build

```bash
$ docker build -t docker-pdftk-webservice .
```

## Run - example
```bash
$ docker run -d -p 80 --name pdftk docker-pdftk-webservice
```

or if you use the pre-build version

```bash
$ docker run -d -p 80 --name pdftk torfsict/docker-pdftk-webservice
```

## Usage

Note: at the moment the only supported action is merging PDF files. Other actions will
be added on request.

### Merge

Post the files you want to merge to the server and get the merged file in return.

```bash
$ curl -F file[]=@file1.pdf -F file[]=@file2.pdf http://localhost/merge > merged.pdf
```

### Ping

Check the status, will return the uptime of the service.

returns

```JavaScript
{
  pong: 18.849
}
```

## License
[MIT](LICENSE)