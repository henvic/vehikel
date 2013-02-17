# Search engine

This search currently indexes the account profiles and posts, but only return the search results of the posts.

There are two daemons: a worker one, which work along Gearman to receive indexing jobs, and a server one, which is a front-end server with some processing which is there to avoid making the Elastic Search (the back-end) available directly.

## Elasticsearch
The search engine back-end is Elasticsearch based. It works on top of Lucene and provides a JSON RESTful interface. There is a lot of quirks to it and it throws a lot of exception if you [do something not expected], but anyways, it is very useful and saves the day.

### Preparing Elasticsearch
For effect of simplicity we assume you just installed ES on localhost.

If you want to redo this process, before proceeding do the following to remove your data:
```
curl -XDELETE http://localhost:9200/posts
```

#### Add the typeahead suggestion analyzer
```
curl -XPOST http://localhost:9200/posts?pretty=1 -d '
{
    "settings" : {
    "index.analysis.filter.suggestions_lowercase.type" : "lowercase",
    "index.version.created" : "200199",
    "index.number_of_replicas" : "1",
    "index.analysis.analyzer.suggestions.filter.0" : "suggestions_lowercase",
    "index.analysis.analyzer.suggestions.tokenizer" : "standard",
    "index.number_of_shards" : "5",
    "index.analysis.filter.suggestions_shingle.max_shingle_size" : "5",
    "index.analysis.filter.suggestions_shingle.type" : "shingle",
    "index.analysis.filter.suggestions_shingle.min_shingle_size" : "2",
    "index.analysis.analyzer.suggestions.filter.1" : "suggestions_shingle"
    }
}
'
```

Verify it with
```
curl -XGET http://localhost:9200/posts/_settings?pretty
```

#### Create the user mapping
```
curl -XPUT http://localhost:9200/posts/user/_mapping?pretty=1 -d '
{
  "user" : {
    "properties" : {
      "account_type" : {
        "type" : "string"
      },
      "address" : {
        "dynamic" : "true",
        "properties" : {
          "country_name" : {
            "type" : "string"
          },
          "locality" : {
            "type" : "string"
          },
          "neighborhood" : {
            "type" : "string"
          },
          "phones" : {
            "dynamic" : "true",
            "properties" : {
              "name" : {
                "type" : "string"
              },
              "tel" : {
                "type" : "string"
              }
            }
          },
          "postal_code" : {
            "type" : "string"
          },
          "region" : {
            "type" : "string"
          },
          "street_address" : {
            "type" : "string"
          }
        }
      },
      "id" : {
        "type" : "long"
      },
      "name" : {
        "type" : "string"
      },
      "picture" : {
        "dynamic" : "true",
        "properties" : {
          "id" : {
            "type" : "string"
          },
          "secret" : {
            "type" : "string"
          }
        }
      },
      "username" : {
        "type" : "string"
      },
      "where" : {
        "type" : "string"
      }
    }
  }
}
'
```

Verify it with
```
curl -XGET http://localhost:9200/posts/user/_mapping?pretty
```

#### Create the post mapping

```
curl -XPUT http://localhost:9200/posts/post/_mapping?pretty=1 -d '
{
    "post" : {
      "properties" : {
        "armor" : {
          "type" : "boolean"
        },
        "creation" : {
          "type" : "string"
        },
        "description" : {
          "type" : "string"
        },
        "description_html_escaped" : {
          "type" : "string"
        },
        "engine" : {
          "type" : "string"
        },
        "equipment" : {
          "type" : "string"
        },
        "fuel" : {
          "type" : "string"
        },
        "id" : {
          "type" : "long"
        },
        "km" : {
          "type" : "integer"
        },
        "make" : {
          "type" : "string"
        },
        "model" : {
          "type" : "string"
        },
        "model_year" : {
          "type" : "short"
        },
        "name" : {
          "type" : "string"
        },
        "pictures" : {
          "dynamic" : "true",
          "properties" : {
            "id" : {
              "type" : "string"
            },
            "secret" : {
              "type" : "string"
            }
          }
        },
        "price" : {
          "type" : "long"
        },
        "status" : {
          "type" : "string"
        },
        "title" : {
          "type" : "multi_field",
          "fields" : {
            "title" : {
              "type" : "string",
              "analyzer" : "standard",
              "include_in_all" : true
            },
            "suggestions" : {
              "type" : "string",
              "analyzer" : "suggestions",
              "include_in_all" : false
            }
          }
        },
        "traction" : {
          "type" : "string"
        },
        "transmission" : {
          "type" : "string"
        },
        "type" : {
          "type" : "string"
        },
        "user" : {
          "dynamic" : "true",
          "properties" : {
            "account_type" : {
              "type" : "string"
            },
            "where" : {
                "type" : "string"
            },
            "address" : {
              "dynamic" : "true",
              "properties" : {
                "country_name" : {
                  "type" : "string"
                },
                "locality" : {
                  "type" : "string"
                },
                "neighborhood" : {
                  "type" : "string"
                },
                "phones" : {
                  "dynamic" : "true",
                  "properties" : {
                    "name" : {
                      "type" : "string"
                    },
                    "tel" : {
                      "type" : "string"
                    }
                  }
                },
                "postal_code" : {
                  "type" : "string"
                },
                "region" : {
                  "type" : "string"
                },
                "street_address" : {
                  "type" : "string"
                }
              }
            },
            "id" : {
              "type" : "string"
            },
            "name" : {
              "type" : "string"
            },
            "picture" : {
              "dynamic" : "true",
              "properties" : {
                "id" : {
                  "type" : "string"
                },
                "secret" : {
                  "type" : "string"
                }
              }
            },
            "username" : {
              "type" : "string"
            }
          }
        }
      }
    }
}
'
```

Verify it with
```
curl -XGET http://localhost:9200/posts/post/_mapping?pretty
```


### Ready to go
After you take these steps you are ready to use ES.

Run the worker to start indexing with
```
node application/search/worker
```

Run the server to start serving the search with
```
node application/search/server
```
